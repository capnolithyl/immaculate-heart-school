"""Structural regression tests for school-only Figma implementation."""
import json
import unittest
from pathlib import Path

THEME = Path(__file__).resolve().parents[1] / "theme"


class SchoolDesignTests(unittest.TestCase):
    def test_school_palette_and_font_inheritance(self):
        data = json.loads((THEME / "theme.json").read_text(encoding="utf-8"))
        colors = {row["slug"]: row["color"] for row in data["settings"]["color"]["palette"]}
        self.assertEqual(colors["navy"], "#024725")
        self.assertEqual(colors["gold"], "#F5CC1A")
        self.assertEqual(colors["background"], "#FFFFFF")
        self.assertNotIn("typography", data["settings"])

    def test_school_template_override_hooks_are_compatible(self):
        header = (THEME / "template-parts/layout/header-content.php").read_text(encoding="utf-8")
        drawer = (THEME / "template-parts/header/utility-nav.php").read_text(encoding="utf-8")
        funcs = (THEME / "functions.php").read_text(encoding="utf-8")
        self.assertIn("data-site-header", header)
        self.assertIn("data-nav-toggle", header)
        self.assertIn('template-parts/header/primary', header)
        self.assertIn("primary-navigation-panel", header)
        self.assertIn("site-nav-drawer__utility", drawer)
        self.assertIn("$args['mobile']", drawer)
        self.assertIn("get_nav_menu_locations()", drawer)
        self.assertIn("ihm_show_mass_schedule", funcs)

    def test_editable_school_specific_options_and_footer(self):
        options = (THEME / "inc/school-options.php").read_text(encoding="utf-8")
        footer = (THEME / "template-parts/layout/footer-content.php").read_text(encoding="utf-8")
        self.assertIn("acf_add_options_sub_page", options)
        self.assertIn("school_drop_off_schedule", options)
        self.assertIn("school_logo", options)
        self.assertIn("get_field( 'school_drop_off_schedule', 'option' )", footer)
        self.assertNotIn("ihm_footer_schedule_groups", footer)
        self.assertTrue((THEME / "assets/images/school-logo.png").is_file())


if __name__ == "__main__":
    unittest.main()
