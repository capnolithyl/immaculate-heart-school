"""Minimal packaging and deployment destination regression tests."""

import importlib.util
import json
import tempfile
import unittest
import zipfile
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]


def load_script(path: str):
    spec = importlib.util.spec_from_file_location(path.replace("/", "_"), ROOT / path)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


build = load_script("scripts/build_zip.py")
deploy = load_script("scripts/deploy_sftp.py")


class SchoolThemeBuildTests(unittest.TestCase):
    def test_school_zip_contains_only_child_theme(self):
        with tempfile.TemporaryDirectory() as temp_dir:
            bundle_path = Path(temp_dir) / "ihm-school.zip"
            build.bundle(bundle_path)
            with zipfile.ZipFile(bundle_path) as archive:
                names = archive.namelist()
                self.assertIn("ihm-school/style.css", names)
                self.assertIn("ihm-school/functions.php", names)
                self.assertIn("ihm-school/assets/css/school.css", names)
                self.assertTrue(all(name.startswith("ihm-school/") for name in names))
                self.assertFalse(any("scripts/" in name or ".vscode" in name for name in names))

    def test_school_css_does_not_include_a_duplicate_reset(self):
        source = (ROOT / "tailwind.css").read_text(encoding="utf-8")
        self.assertIn("source(none)", source)
        self.assertIn('@source "./theme"', source)
        self.assertNotIn('@import "tailwindcss";', source)
        self.assertNotIn('@import "tailwindcss/preflight', source)

    def test_bundle_rejects_missing_compiled_styles(self):
        with tempfile.TemporaryDirectory() as temp_dir:
            previous = build.THEME_DIR
            try:
                build.THEME_DIR = Path(temp_dir)
                (build.THEME_DIR / "style.css").write_text(
                    "/* Theme Name: Test\\nTemplate: ihm */", encoding="utf-8"
                )
                (build.THEME_DIR / "functions.php").write_text("<?php", encoding="utf-8")
                with self.assertRaisesRegex(ValueError, "compiled school stylesheet"):
                    build.bundle(Path(temp_dir) / "test.zip")
            finally:
                build.THEME_DIR = previous

    def test_child_declares_correct_parent(self):
        header = (ROOT / "theme" / "style.css").read_text(encoding="utf-8")
        self.assertIn("Template: ihm", header)


class SchoolThemeDeployTests(unittest.TestCase):
    def test_only_school_target_is_accepted(self):
        path = "/var/www/school/wp-content/themes/ihm-school"
        self.assertEqual(deploy.validated_deploy_path(path), path)

    def test_reject_parent_and_non_school_directories(self):
        invalid = [
            "/var/www/school/wp-content/themes/ihm",
            "/var/www/parish/wp-content/themes/ihm",
            "/var/www/school/wp-content/themes/ihm-school/../ihm",
            "wp-content/themes/ihm-school",
            "/var/www/school/wp-content/themes/",
        ]
        for path in invalid:
            with self.subTest(path=path), self.assertRaises(SystemExit):
                deploy.validated_deploy_path(path)

    def test_config_reads_school_target_only(self):
        with tempfile.TemporaryDirectory() as temp_dir:
            config = Path(temp_dir) / "sftp.json"
            config.write_text(json.dumps({
                "protocol": "sftp",
                "host": "school.example.com",
                "username": "user",
                "password": "secret",
                "remotePath": "/srv/school/wp-content/themes/ihm-school",
            }), encoding="utf-8")
            self.assertEqual(
                deploy.load_configuration(config)["deployPath"],
                "/srv/school/wp-content/themes/ihm-school",
            )


if __name__ == "__main__":
    unittest.main()
