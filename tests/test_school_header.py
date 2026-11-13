"""Regression tests for the school-specific single navigation and contact strip.

These tests guard the child/parent boundary without mocking WordPress core.
"""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
THEME = ROOT / "theme"


class SchoolHeaderTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.functions = (THEME / "functions.php").read_text(encoding="utf-8")
        cls.header = (THEME / "template-parts/layout/header-content.php").read_text(
            encoding="utf-8"
        )
        cls.desktop = (THEME / "template-parts/header/utility-nav.php").read_text(
            encoding="utf-8"
        )
        cls.mobile = (THEME / "template-parts/header/primary-nav.php").read_text(
            encoding="utf-8"
        )
        cls.styles = (THEME / "style.css").read_text(encoding="utf-8")

    def test_single_menu_source_is_shared_by_desktop_and_mobile(self):
        self.assertIn("function ihms_navigation_location()", self.functions)
        self.assertIn("has_nav_menu( 'header' )", self.functions)
        self.assertIn("has_nav_menu( 'primary' )", self.functions)
        self.assertIn("'header';", self.functions)
        self.assertIn("return has_nav_menu( 'primary' ) ? 'primary' : '';", self.functions)
        self.assertIn("ihms_navigation_location()", self.desktop)
        self.assertIn("ihms_navigation_location()", self.mobile)
        self.assertIn("'theme_location' => $ihms_menu_location", self.desktop)
        self.assertIn("'theme_location' => $ihms_menu_location", self.mobile)
        self.assertNotIn("get_template_part( 'template-parts/header/utility'", self.mobile)
        self.assertNotIn("site-nav-drawer__utility", self.mobile)
        self.assertNotIn("wp_get_nav_menu_items(", self.desktop)

    def test_desktop_has_inline_menu_and_no_hamburger(self):
        self.assertIn("ihms-header__links hidden min-w-0 lg:block", self.desktop)
        self.assertIn("'depth'          => 2", self.desktop)
        self.assertRegex(
            self.header,
            r'class="[^"]*lg:hidden"[^>]*aria-controls="primary-navigation-panel"',
        )
        self.assertEqual(self.header.count("data-nav-toggle"), 1)
        self.assertIn("get_template_part( 'template-parts/header/utility', 'nav' )", self.header)
        self.assertIn("ihms-header__menu", self.styles)

    def test_mobile_drawer_reuses_parent_javascript_contract(self):
        for marker in (
            'id="primary-navigation-panel"',
            "data-nav-drawer",
            "data-nav-stack",
            "data-nav-root",
            "data-nav-menu",
            'id="primary-menu"',
            "data-nav-submenus",
            "data-nav-close",
        ):
            if marker == 'id="primary-menu"':
                self.assertIn("'menu_id'        => 'primary-menu'", self.mobile)
            else:
                self.assertIn(marker, self.mobile)
        self.assertIn("font-body lg:hidden", self.mobile)
        self.assertNotIn("site-nav-drawer__utility", self.mobile)
        self.assertIn("ihms_social_links(", self.mobile)
        self.assertIn("wp_nav_menu(", self.mobile)
        self.assertNotIn("get_template_directory()", self.mobile)

    def test_contact_bar_uses_existing_acf_settings(self):
        self.assertIn("get_field( 'parish_address', 'option' )", self.header)
        self.assertIn("get_field( 'parish_phone', 'option' )", self.header)
        self.assertIn("get_field( 'parish_directions_url', 'option' )", self.header)
        self.assertIn("get_field( 'social_media_links', 'option' )", self.functions)
        self.assertIn('href="tel:<?php echo esc_attr( $ihms_tel ); ?>"', self.header)
        self.assertIn("ihms_social_links( 'ihms-header__social", self.header)
        self.assertIn("lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)]", self.header)
        self.assertNotIn("hidden sm:flex", self.header)
        self.assertNotIn("sm:inline-flex", self.header)
        self.assertNotIn("330-929-8361", self.header)
        self.assertNotIn("ihmcfo", self.functions)

    def test_social_renderer_works_without_font_awesome_assets(self):
        for key in ("facebook", "instagram", "youtube"):
            self.assertIn("strpos( $identifier, '" + key + "' )", self.functions)
        self.assertIn("function ihms_social_icon_markup(", self.functions)
        self.assertIn("function ihms_social_links(", self.functions)
        self.assertIn("wp_kses( $icon", self.functions)
        self.assertIn("esc_url( $url )", self.functions)
        self.assertIn('rel="noopener noreferrer"', self.functions)
        self.assertIn("focus-visible:outline-gold", self.functions)

    def test_mobile_drawer_closes_on_desktop_resize(self):
        script = (THEME / "assets/js/school-navigation.js").read_text(
            encoding="utf-8"
        )
        self.assertIn("matchMedia('(min-width: 64rem)')", script)
        self.assertIn("drawer.querySelector('[data-nav-close]')?.click()", script)
        self.assertIn("breakpoint.addEventListener('change', closeOnDesktop)", script)
        self.assertNotIn("data-nav-toggle", script)
        self.assertIn("'ihms-navigation'", self.functions)
        self.assertIn("array( 'ihm-script' )", self.functions)
        self.assertIn("/assets/js/school-navigation.js", self.functions)

    def test_parent_theme_is_only_a_dependency(self):
        self.assertIn("Template: ihm", self.styles)
        self.assertIn("get_template_part( 'template-parts/header/primary', 'nav' )", self.header)
        self.assertNotIn("script.js", self.mobile)
        self.assertEqual(self.functions.count("function ihms_navigation_location()"), 1)

    def test_version_matches(self):
        defined = re.search(r"define\( 'IHMS_VERSION', '([^']+)' \)", self.functions)
        declared = re.search(r"Version: (\d+\.\d+\.\d+)", self.styles)
        self.assertIsNotNone(defined)
        self.assertIsNotNone(declared)
        self.assertEqual(defined.group(1), declared.group(1))


if __name__ == "__main__":
    unittest.main()
