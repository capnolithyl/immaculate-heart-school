"""Regression coverage for deploying the IHM parent to the school installation."""

from __future__ import annotations

import json
import sys
import tempfile
import unittest
import zipfile
from pathlib import Path
from unittest.mock import patch

from scripts import deploy_parent


class ParentDeployTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        self.config_path = self.root / "sftp.json"
        self.parent = self.root / "immaculate-heart-of-mary"

        self.parent.mkdir()
        (self.parent / "scripts").mkdir()
        (self.parent / "package.json").write_text('{"scripts":{"bundle":"echo test"}}', encoding="utf-8")
        (self.parent / "scripts" / "deploy_sftp.py").write_text("# IHM deployer", encoding="utf-8")
        self.write_config("/srv/school/wp-content/themes/ihm-school")
        self.write_zip(["ihm/style.css", "ihm/functions.php"])

    def write_config(self, remote_path: str):
        self.config_path.write_text(json.dumps({
            "protocol": "sftp",
            "host": "school.example.com",
            "username": "school-user",
            "password": "secret",
            "remotePath": remote_path,
        }), encoding="utf-8")

    def write_zip(self, paths):
        with zipfile.ZipFile(self.parent / "ihm.zip", "w") as archive:
            for path in paths:
                archive.writestr(path, "placeholder")

    def test_school_config_is_used_for_parent_build_and_upload(self):
        with patch.object(deploy_parent.shutil, "which", return_value="npm"), \
             patch.object(deploy_parent.subprocess, "run") as run:
            deploy_parent.deploy_parent(self.parent, self.config_path)

        self.assertEqual(run.call_count, 2)
        build, upload = run.call_args_list

        self.assertEqual(build.args[0], ["npm", "run", "bundle"])
        self.assertEqual(build.kwargs["cwd"], self.parent)
        self.assertTrue(build.kwargs["check"])

        self.assertEqual(
            upload.args[0],
            [sys.executable, str(self.parent / "scripts" / "deploy_sftp.py")],
        )
        self.assertEqual(upload.kwargs["cwd"], self.parent)
        self.assertTrue(upload.kwargs["check"])
        self.assertEqual(upload.kwargs["env"]["DEPLOY_SFTP_CONFIG"], str(self.config_path))
        self.assertEqual(upload.kwargs["env"]["DEPLOY_ZIP_PATH"], str(self.parent / "ihm.zip"))

    def test_refuses_parish_or_parent_theme_config_before_running_any_commands(self):
        for path in (
            "/srv/parish/wp-content/themes/ihm",
            "/srv/school/wp-content/themes/ihm",
            "/srv/school/wp-content/themes/ihm-school/../ihm",
        ):
            with self.subTest(remotePath=path):
                self.write_config(path)
                with patch.object(deploy_parent.subprocess, "run") as run, \
                     self.assertRaises(SystemExit):
                    deploy_parent.deploy_parent(self.parent, self.config_path)
                run.assert_not_called()

    def test_refuses_missing_parent_source(self):
        with patch.object(deploy_parent.subprocess, "run") as run, \
             self.assertRaises(SystemExit):
            deploy_parent.deploy_parent(self.root / "missing", self.config_path)
        run.assert_not_called()

    def test_invalid_parent_zip_does_not_upload(self):
        for paths in (
            ["ihm/style.css"],
            ["ihm/style.css", "ihm/functions.php", "ihm-school/style.css"],
        ):
            with self.subTest(paths=paths):
                self.write_zip(paths)
                with patch.object(deploy_parent.shutil, "which", return_value="npm"), \
                     patch.object(deploy_parent.subprocess, "run") as run, \
                     self.assertRaises(SystemExit):
                    deploy_parent.deploy_parent(self.parent, self.config_path)
                self.assertEqual(run.call_count, 1)
                self.assertEqual(run.call_args.args[0], ["npm", "run", "bundle"])

    def test_build_failure_does_not_attempt_upload(self):
        def fail_build(*args, **kwargs):
            raise deploy_parent.subprocess.CalledProcessError(1, args[0])

        with patch.object(deploy_parent.shutil, "which", return_value="npm"), \
             patch.object(deploy_parent.subprocess, "run", side_effect=fail_build) as run, \
             self.assertRaises(deploy_parent.subprocess.CalledProcessError):
            deploy_parent.deploy_parent(self.parent, self.config_path)
        self.assertEqual(run.call_count, 1)


if __name__ == "__main__":
    unittest.main()
