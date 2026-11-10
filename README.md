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
npm run deploy        # Bundle + SFTP upload, without a git push
npm run ship          # Bundle, commit pending changes, push main, SFTP upload
```

Requires Node.js for Tailwind/PostCSS/npm/ship and Python 3.9+ for ZIP/SFTP scripts. Run `npm install` after pulling. The parent theme requires its own separate build and install; the school does not compile its PHP blocks or shared CSS.

## School SFTP deployment

Create **this repository's own** `.vscode/sftp.json` (gitignored). Use `config/sftp.example.json` as a guide; do not commit server passwords.

The `remotePath` must be the **school installation's** absolute path ending in `/wp-content/themes/ihm-school`. The script refuses `/wp-content/themes/ihm` and other destinations. This prevents the child deployment from overwriting the parent's files, but you must still double-check that the host and earlier directories point to the **school** WordPress installation.

The deployment workflow mirrors the parish's SFTP uploader: extract the ZIP, upload its files, then remove obsolete files **only within the child theme directory**. You can override the config path with `DEPLOY_SFTP_CONFIG` and the ZIP location with `DEPLOY_ZIP_PATH`.

`npm run deploy` does not upload or modify the parent theme. Install/update the parent separately using its own release/deploy workflow.

The ZIP and local credentials are ignored by Git.
