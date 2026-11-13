# Immaculate Heart of Mary Grade School

A small WordPress **child theme** of the parish's [IHM parent theme](https://github.com/capnolithyl/immaculate-heart-of-mary). The school has its own independent WordPress installation: **no multisite and no duplicated parent theme code**.

## Install

1. Install the **compiled** parish theme as `wp-content/themes/ihm/` on the school WordPress site. Its production ZIP is `ihm.zip`.
2. Run `npm install` once, then `npm run bundle` from this repository. It builds the school-only Tailwind utilities and creates `ihm-school.zip`, containing `ihm-school/style.css`, `ihm-school/functions.php`, and `ihm-school/assets/css/school.css`.
3. Install the school ZIP through **Appearance → Themes → Add New → Upload Theme**, then activate **IHM Grade School**. Both parent and child must remain installed.
4. Set menus, site title, homepage, ACF options and content independently in the school WordPress dashboard.

The repository keeps its installable WordPress theme in `theme/`, following the parish's source/build separation. Do not install the **repository root** as a theme.

## Development

- `theme/style.css`: WordPress child-theme header and hand-written school-only overrides.
- `tailwind.css`: Tailwind v4 input for **school-only utility classes**. It scans child templates under `theme/`, has the same WordPress preset token aliases as the parent, and deliberately **does not import Preflight or the parent build**.
- `theme/assets/css/school.css`: generated CSS, gitignored and added automatically to the production ZIP. Do not edit this output.
- `postcss.config.mjs`: school-only PostCSS/Tailwind configuration.
- `theme/functions.php`: child-only hooks. The parent handles blocks, navigation functionality, reusable PHP and JavaScript.
- Add an identically named file under `theme/template-parts/` only when the school needs different markup. WordPress's `get_template_part()` will prefer the child copy.
- Colors: the parent already maps Tailwind palette values to WordPress preset CSS variables. Once the final school palette is confirmed, define the school values in a child `theme.json` and test both the site and editor. Keep the existing palette slugs available to parent components; a child palette array may replace the parent's palette entries.
- Do **not** copy the parent's blocks or CSS build into this repository.

The parent already enqueues its compiled stylesheet via `get_template_directory_uri() . '/style.css'` (IHM 0.7.79+). The school then enqueues its own `style.css` and the small generated utility file. The generated file also loads in the WordPress editor.

## Commands

```powershell
npm install           # Install Tailwind/PostCSS dependencies once
npm run dev           # Watch school PHP templates and regenerate child CSS
npm run watch         # Alias of dev
npm run build:css     # Compile only school-specific Tailwind utilities
npm run bundle        # Compile CSS and build ihm-school.zip
npm run test          # Python package/deployment safety tests
npm run deploy:setup  # Install Python Paramiko once
npm run deploy        # Build/upload the SCHOOL CHILD theme only
npm run deploy:parent # Build/upload the shared IHM PARENT to the school site
npm run deploy:all    # Build/upload parent, then child, to the school site
npm run ship          # Bundle, commit/push SCHOOL changes, upload child only
```

Requires Node.js for Tailwind/PostCSS/npm/ship and Python 3.9+ for ZIP/SFTP scripts. Run `npm install` after pulling. The parent theme requires its own separate build and install; the school does not compile its PHP blocks or shared CSS.

## School SFTP deployment

Create **this repository's own** `.vscode/sftp.json` (gitignored). Use `config/sftp.example.json` as a guide; do not commit server passwords.

The `remotePath` must be the **school installation's** absolute path ending in `/wp-content/themes/ihm-school`. The script refuses `/wp-content/themes/ihm` and other destinations. This prevents the child deployment from overwriting the parent's files, but you must still double-check that the host and earlier directories point to the **school** WordPress installation.

The normal child deployment mirrors the parish's SFTP uploader: extract the ZIP, upload its files, then remove obsolete files **only within the child theme directory**. You can override the config path with `DEPLOY_SFTP_CONFIG` and the ZIP location with `DEPLOY_ZIP_PATH`.

### Deploy the parent to the school site

Keep the two repositories next to one another:

```text
Immaculate Heart of Mary - Cuyahoga, OH/
├── immaculate-heart-of-mary/     # shared parent source
└── immaculate-heart-school/      # school child source + school SFTP config
```

From the **school** repository, run `npm run deploy:parent`. This command:

1. Validates **the school's** `.vscode/sftp.json` (its `remotePath` must end in `/wp-content/themes/ihm-school`).
2. Runs `npm run bundle` inside the sibling **parent** repository. Install the parent's Node dependencies with `npm install` in that directory first.
3. Validates the generated parent `ihm.zip` contains only the `ihm/` theme and the expected WordPress files.
4. Invokes the **existing parent SFTP uploader**, passing the school's config and parent ZIP as environment overrides. It deploys to the **school installation's** `wp-content/themes/ihm/`, leaving `ihm-school/` untouched.

To deploy both in the correct order, run `npm run deploy:all`; this runs parent deployment followed by the usual child deployment. `npm run deploy` and `npm run ship` remain **child-only**. Neither new command commits or pushes to the parent repository, and neither deploys to the parish unless you accidentally point the *school's* SFTP credentials/path there.

**Check the actual SFTP host and web root carefully** before deploying: matching `/wp-content/themes/ihm-school` alone cannot prove an SFTP server belongs to the school. The parent upload will synchronize/prune obsolete files **inside the school's `ihm/` directory**. If `DEPLOY_SFTP_CONFIG` is set, it must point to the school's configuration, not the parish's.

The parent still has its own independent release and deployment workflow for the parish website.

The ZIP and local credentials are ignored by Git.

## School Figma implementation (v0.3.2)

The school child theme owns all school-specific changes. The parish parent repository is unchanged.

- `theme/theme.json` overrides the shared WordPress palette slugs with the Figma school green `#024725`, yellow `#F5CC1A`, and white background. Existing parent Tailwind classes inherit these tokens.
- `theme/template-parts/layout/header-content.php` uses the Figma green contact strip with the **phone and ACF social links visible**, plus the school logo and translucent navigation. The hamburger is **mobile-only**; on desktop there is one horizontal menu with no parish-style second navigation.
- `theme/template-parts/header/utility-nav.php` (desktop) and `theme/template-parts/header/primary-nav.php` (mobile) render **the same WordPress menu**. Assign it under **Appearance → Menus → Manage Locations → Header Links**. If that location is unassigned, the theme temporarily falls back to Primary for compatibility, but never renders both menus. The mobile drawer keeps the parent's submenu, Escape and focus-management JavaScript. The separate parish Primary menu is not shown alongside Header Links.
- `theme/assets/js/school-navigation.js` is a tiny school-only resize guard. It closes the mobile drawer via the parent's own close handler when the viewport grows to desktop size; no separate drawer implementation is introduced.
- `theme/template-parts/layout/footer-content.php` replaces the parish-specific Mass Schedule column with a school Drop Off Schedule and contact details, while reusing the existing WordPress footer menu and social settings.
- `theme/inc/school-options.php` adds **Theme Settings → School** once ACF Pro is active: optional logo override, Schedule a Tour link and editable drop-off times. Set **Theme Settings → Theme Settings → Parish Information → Phone / Address** and **Social Media → Social Media Links** for the top bar and footer. These are shared field definitions but values belong to the school installation. Facebook, Instagram and YouTube icons render even when the Font Awesome field isn't populated; links still require an ACF URL.
- The parent theme's floating Mass Schedule is disabled on the school site through its documented filter.
- The parish-only **Mass Times admin page** is also disabled on the school site through `ihm_enable_mass_times_options` (requires parent theme **v0.7.81+**). The parent registers its Theme Settings root before its Footer and School subpages to prevent broken submenu links. The school keeps **Theme Settings → Theme Settings, Footer, School** and does not offer Mass Times. If the School submenu is missing, check that the current child theme is active and ACF Pro is active.

The Figma logo is included locally in `theme/assets/images/school-logo.png`; there are no runtime Figma URLs.

### Follow-up content

Use the reviewed Remote WP Bridge JSON seed plan separately. Activate ACF Pro and other school plugins before importing ACF content/options; the current schema reports ACF inactive. The seed uses WordPress native draft content and menus so it can be imported without requiring fabricated images or staff biographies.
