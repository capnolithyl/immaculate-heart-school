"""School ACF options/menu wiring must stay child-owned and parish-safe."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class SchoolOptionsMenuTests(unittest.TestCase):
    def test_school_disables_parish_only_mass_times(self):
        source = (ROOT / "theme" / "functions.php").read_text(encoding="utf-8")
        self.assertIn(
            "add_filter( 'ihm_enable_mass_times_options', '__return_false' );",
            source,
        )
        self.assertIn(
            "add_filter( 'ihm_show_mass_schedule', '__return_false' );",
            source,
        )
        self.assertNotIn("remove_menu_page(", source)

    def test_school_options_remain_nested_under_shared_parent(self):
        source = (ROOT / "theme" / "inc" / "school-options.php").read_text(
            encoding="utf-8"
        )
        self.assertRegex(source, r"'menu_slug'\s*=>\s*'ihms-school-settings'")
        self.assertRegex(source, r"'parent_slug'\s*=>\s*'theme-settings'")
        self.assertRegex(
            source,
            r"add_action\( 'acf/init', 'ihms_register_school_settings', 25 \)",
        )

    def test_child_versions_match(self):
        functions = (ROOT / "theme" / "functions.php").read_text(encoding="utf-8")
        styles = (ROOT / "theme" / "style.css").read_text(encoding="utf-8")
        defined = re.search(r"define\( 'IHMS_VERSION', '([^']+)' \)", functions)
        declared = re.search(r"Version: (\d+\.\d+\.\d+)", styles)
        self.assertIsNotNone(defined)
        self.assertIsNotNone(declared)
        self.assertEqual(defined.group(1), declared.group(1))


if __name__ == "__main__":
    unittest.main()
