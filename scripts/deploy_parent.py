#!/usr/bin/env python3
"""Build the sibling IHM parent theme and deploy it to the SCHOOL WordPress site.

Reuses the parent's own production build and SFTP uploader. The school site's
SFTP credentials and remotePath come exclusively from this child repository.
"""

from __future__ import annotations

import os
import posixpath
import shutil
import subprocess
import sys
import zipfile
from pathlib import Path

from .deploy_sftp import CONFIG_PATH, REPO_ROOT, fail, load_configuration


PARENT_REPO = (REPO_ROOT.parent / "immaculate-heart-of-mary").resolve()
PARENT_SLUG = "ihm"


def validate_parent_bundle(zip_path: Path) -> None:
    """Refuse to install anything except the production IHM parent theme."""
    if not zip_path.is_file():
        fail(f"Parent ZIP not found after build: {zip_path}")

    try:
        with zipfile.ZipFile(zip_path) as archive:
            files = set(archive.namelist())
            if not {"ihm/style.css", "ihm/functions.php"}.issubset(files):
                fail("Parent ZIP must contain ihm/style.css and ihm/functions.php")

            if any(
                not name.startswith("ihm/")
                or ".." in name.split("/")
                for name in files
            ):
                fail("Parent ZIP contains entries outside the ihm/ theme folder")
    except (OSError, zipfile.BadZipFile) as exc:
        fail(f"Invalid parent ZIP: {exc}")


def deploy_parent(
    parent_repo: Path = PARENT_REPO,
    config_path: Path = CONFIG_PATH,
) -> None:
    """Build, validate, and upload the parent without touching the parish site."""
    # The child's loader enforces the school installation's child-theme path:
    # .../wp-content/themes/ihm-school. The parent's existing uploader then
    # resolves the sibling .../wp-content/themes/ihm using that same config.
    config_path = config_path.resolve()
    config = load_configuration(config_path)
    school_child_path = config["deployPath"]
    school_parent_path = posixpath.join(posixpath.dirname(school_child_path), PARENT_SLUG)

    parent_repo = parent_repo.resolve()
    parent_package = parent_repo / "package.json"
    parent_uploader = parent_repo / "scripts" / "deploy_sftp.py"
    parent_zip = parent_repo / "ihm.zip"

    if parent_repo == REPO_ROOT or not parent_package.is_file() or not parent_uploader.is_file():
        fail(
            "IHM parent source not found. Clone immaculate-heart-of-mary alongside "
            "immaculate-heart-school, or restore its package.json and deploy script."
        )

    npm_executable = shutil.which("npm")
    if npm_executable is None:
        fail("npm was not found. Install Node.js before deploying the parent theme.")

    print(f"Building parent theme from: {parent_repo}", flush=True)
    print(f"School SFTP host: {config['host']}", flush=True)
    print(f"School parent-theme destination: {school_parent_path}", flush=True)
    print("This updates the SCHOOL parent theme only, not the parish site.", flush=True)

    # Build the parent in its own repository so its Tailwind/JS pipelines
    # remain the single source of truth.
    subprocess.run([npm_executable, "run", "bundle"], cwd=parent_repo, check=True)
    validate_parent_bundle(parent_zip)

    # Parent uploader understands the IHM ZIP format and always deploys to
    # a canonical /wp-content/themes/ihm target. Point it at the school's
    # independent credentials instead of the parish repo's .vscode config.
    env = os.environ.copy()
    env["DEPLOY_SFTP_CONFIG"] = str(config_path)
    env["DEPLOY_ZIP_PATH"] = str(parent_zip)
    subprocess.run(
        [sys.executable, str(parent_uploader)],
        cwd=parent_repo,
        env=env,
        check=True,
    )


def main() -> None:
    try:
        deploy_parent()
    except subprocess.CalledProcessError as exc:
        fail(f"Parent theme build or school upload failed (exit code {exc.returncode}).")


if __name__ == "__main__":
    main()
