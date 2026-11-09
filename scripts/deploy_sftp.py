#!/usr/bin/env python3
"""Deploy only the school child-theme ZIP via SFTP.

Uses the same basic workflow as the parish theme, but requires a destination
ending in /wp-content/themes/ihm-school to avoid damaging the parent theme.
"""

from __future__ import annotations

import json
import os
import posixpath
import stat
import sys
import tempfile
import zipfile
from pathlib import Path, PurePosixPath
from typing import Any


REPO_ROOT = Path(__file__).resolve().parents[1]
CONFIG_PATH = Path(
    os.environ.get("DEPLOY_SFTP_CONFIG", REPO_ROOT / ".vscode" / "sftp.json")
).resolve()
ZIP_PATH = Path(
    os.environ.get("DEPLOY_ZIP_PATH", REPO_ROOT / "ihm-school.zip")
).resolve()
THEME_SLUG = "ihm-school"


def fail(message: str) -> None:
    print(f"Deployment aborted: {message}", file=sys.stderr)
    raise SystemExit(1)


def validated_deploy_path(path: str) -> str:
    """Accept only an absolute, normalized path to this child-theme folder."""
    if not path.startswith("/") or path != posixpath.normpath(path):
        fail("remotePath must be absolute and normalized (no . or .. segments)")

    parts = PurePosixPath(path).parts
    if len(parts) < 5 or parts[-3:] != ("wp-content", "themes", THEME_SLUG):
        fail("remotePath must end in /wp-content/themes/ihm-school (school installation only)")

    return path


def load_configuration(config_path: Path = CONFIG_PATH) -> dict[str, Any]:
    if not config_path.is_file():
        fail(f"SFTP config not found: {config_path}. Create .vscode/sftp.json.")

    try:
        config = json.loads(config_path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        fail(f"could not read SFTP config: {exc}")

    if not isinstance(config, dict) or config.get("protocol") != "sftp":
        fail("SFTP config must be a JSON object using protocol 'sftp'")

    required = ("host", "username", "password", "remotePath")
    missing = [field for field in required if not config.get(field)]
    if missing:
        fail(f"missing required SFTP setting(s): {', '.join(missing)}")

    try:
        config["port"] = int(config.get("port", 22))
    except (TypeError, ValueError):
        fail("SFTP port must be a number")

    config["deployPath"] = validated_deploy_path(str(config["remotePath"]))
    return config


def extract_bundle(zip_path: Path = ZIP_PATH) -> tuple[tempfile.TemporaryDirectory[str], Path]:
    if not zip_path.is_file():
        fail(f"production bundle not found: {zip_path}. Run npm run bundle first.")

    temp_dir = tempfile.TemporaryDirectory(prefix="ihm-school-deploy-")
    root = Path(temp_dir.name).resolve()

    try:
        with zipfile.ZipFile(zip_path) as archive:
            for entry in archive.infolist():
                entry_path = PurePosixPath(entry.filename)
                if entry_path.is_absolute() or ".." in entry_path.parts:
                    fail(f"unsafe ZIP path: {entry.filename}")
                if not entry_path.parts or entry_path.parts[0] != THEME_SLUG:
                    fail(f"unexpected root directory in ZIP: {entry.filename}")

            archive.extractall(root)
    except (OSError, zipfile.BadZipFile) as exc:
        temp_dir.cleanup()
        fail(f"could not extract production bundle: {exc}")

    local_root = root / THEME_SLUG
    if not (local_root / "style.css").is_file() or not (local_root / "functions.php").is_file():
        temp_dir.cleanup()
        fail("ZIP must contain ihm-school/style.css and ihm-school/functions.php")

    return temp_dir, local_root


def remote_lstat(sftp: Any, path: str) -> Any:
    try:
        return sftp.lstat(path)
    except OSError:
        return None


def remove_remote_tree(sftp: Any, path: str) -> None:
    attributes = sftp.lstat(path)
    if stat.S_ISDIR(attributes.st_mode) and not stat.S_ISLNK(attributes.st_mode):
        for item in sftp.listdir_attr(path):
            remove_remote_tree(sftp, posixpath.join(path, item.filename))
        sftp.rmdir(path)
    else:
        sftp.remove(path)


def ensure_child_directory(sftp: Any, root: str) -> None:
    """Create only the child directory; never create/modify the parent themes directory."""
    themes_root = posixpath.dirname(root)
    parent_attrs = remote_lstat(sftp, themes_root)
    if parent_attrs is None or not stat.S_ISDIR(parent_attrs.st_mode):
        fail(f"WordPress themes directory does not exist: {themes_root}")

    attrs = remote_lstat(sftp, root)
    if attrs is None:
        sftp.mkdir(root)
    elif stat.S_ISLNK(attrs.st_mode) or not stat.S_ISDIR(attrs.st_mode):
        fail(f"child theme target is not a real directory: {root}")


def upload_tree(sftp: Any, local_dir: Path, remote_dir: str, local_root: Path) -> None:
    for local_path in sorted(local_dir.iterdir(), key=lambda item: item.name):
        if local_path.is_symlink():
            fail(f"refusing to upload local symlink: {local_path}")

        remote_path = posixpath.join(remote_dir, local_path.name)
        attrs = remote_lstat(sftp, remote_path)

        if local_path.is_dir():
            if attrs is None:
                sftp.mkdir(remote_path)
            elif stat.S_ISLNK(attrs.st_mode) or not stat.S_ISDIR(attrs.st_mode):
                fail(f"refusing to traverse non-directory: {remote_path}")
            upload_tree(sftp, local_path, remote_path, local_root)
            continue

        if attrs is not None:
            if stat.S_ISDIR(attrs.st_mode) and not stat.S_ISLNK(attrs.st_mode):
                remove_remote_tree(sftp, remote_path)
            elif stat.S_ISLNK(attrs.st_mode):
                sftp.remove(remote_path)

        print(f"Uploading {local_path.relative_to(local_root).as_posix()}")
        sftp.put(str(local_path), remote_path)


def prune_tree(sftp: Any, local_dir: Path, remote_dir: str, remote_root: str) -> None:
    local_names = {path.name for path in local_dir.iterdir()}
    for entry in sftp.listdir_attr(remote_dir):
        remote_path = posixpath.join(remote_dir, entry.filename)
        local_path = local_dir / entry.filename
        if entry.filename not in local_names:
            print(f"Removing stale {remote_path.removeprefix(remote_root).lstrip('/')}")
            remove_remote_tree(sftp, remote_path)
        elif stat.S_ISDIR(entry.st_mode) and not stat.S_ISLNK(entry.st_mode) and local_path.is_dir():
            prune_tree(sftp, local_path, remote_path, remote_root)


def main() -> None:
    try:
        import paramiko
    except ModuleNotFoundError:
        fail("Paramiko is not installed. Run npm run deploy:setup first.")

    config = load_configuration()
    temp_dir, local_root = extract_bundle()
    remote_root = config["deployPath"]

    print(f"Using school SFTP config: {CONFIG_PATH}")
    print(f"School child-theme target: {remote_root}")
    print("Connecting to deployment server...")

    client = paramiko.SSHClient()
    client.load_system_host_keys()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    try:
        client.connect(
            hostname=config["host"],
            port=config["port"],
            username=config["username"],
            password=config["password"],
            look_for_keys=False,
            allow_agent=False,
            timeout=30,
            auth_timeout=30,
            banner_timeout=30,
        )

        with client.open_sftp() as sftp:
            ensure_child_directory(sftp, remote_root)
            upload_tree(sftp, local_root, remote_root, local_root)
            prune_tree(sftp, local_root, remote_root, remote_root)
    finally:
        client.close()
        temp_dir.cleanup()

    print("School child-theme deployment complete.")


if __name__ == "__main__":
    main()
