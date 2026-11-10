#!/usr/bin/env python3
"""Package only theme/ into the deployable ihm-school.zip archive."""

from __future__ import annotations

import os
import sys
import zipfile
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[1]
THEME_DIR = REPO_ROOT / "theme"
THEME_SLUG = "ihm-school"
ZIP_PATH = Path(os.environ.get("DEPLOY_ZIP_PATH", REPO_ROOT / f"{THEME_SLUG}.zip")).resolve()


def bundle(zip_path: Path = ZIP_PATH) -> Path:
    """Build a ZIP with the canonical WordPress child-theme directory."""
    style_file = THEME_DIR / "style.css"
    functions_file = THEME_DIR / "functions.php"
    utilities_file = THEME_DIR / "assets" / "css" / "school.css"

    if not style_file.is_file() or not functions_file.is_file():
        raise ValueError("theme/style.css and theme/functions.php must exist")

    if not utilities_file.is_file() or not utilities_file.stat().st_size:
        raise ValueError("compiled school stylesheet is missing. Run npm run build:css first.")

    header = style_file.read_text(encoding="utf-8")
    if "Template: ihm" not in header or "Theme Name:" not in header:
        raise ValueError("child theme must declare Theme Name and Template: ihm")

    paths = sorted(path for path in THEME_DIR.rglob("*") if path.is_file())
    if any(path.is_symlink() for path in THEME_DIR.rglob("*")):
        raise ValueError("symlinks are not supported in the production bundle")

    zip_path.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(zip_path, "w", compression=zipfile.ZIP_DEFLATED) as archive:
        for path in paths:
            archive.write(path, arcname=f"{THEME_SLUG}/{path.relative_to(THEME_DIR).as_posix()}")

    return zip_path


if __name__ == "__main__":
    try:
        print(f"Created school theme ZIP: {bundle()}")
    except (OSError, ValueError, zipfile.BadZipFile) as exc:
        print(f"Bundle aborted: {exc}", file=sys.stderr)
        raise SystemExit(1) from exc
