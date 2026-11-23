# Handover — keeping processmining.nu running without Claude

For colleagues who need to change or republish the website when Claude is not available.

## Where everything lives

| What | Where |
|---|---|
| Source and history | GitHub: `jorishill620/processmining-website` (private) |
| The site (static) | `Website Code/` — built pages in the folder itself, sources in `_src/` |
| The WordPress plugin | `WordPress/pm-site.zip` (readable copy: `WordPress/pm-site/`) |
| Upload routine and version log | `RELEASES.md` and `WordPress/INSTALL.md` |
| Brand (logos) and the concept | `Brand/`, `Concept/CONCEPT.md` |

## Access that has to be arranged by a person (Claude cannot grant it)

Fill in the "Who" column and keep logins in the company password manager, not in this file.

| Access | Why it matters | How | Who |
|---|---|---|---|
| GitHub repository | Change text, get earlier versions | Repository → Settings → Collaborators → add people with *Write* (or move the repo to a company GitHub organisation, so it does not depend on one personal account) | |
| WordPress admin (processmining.nu) | Upload the plugin, press *Connect all pages*, check pages | Users → Add new → role *Administrator*, one named account per person | |
| Hosting / domain registrar | Domain, DNS, certificates, e-mail `info@processmining.nu` | Add a second owner or admin at the provider | |

## Changing text (no Claude needed)

1. Find the sentence: it lives in `Website Code/_src/nl/<page>.html` (Dutch) and `Website Code/_src/en/<page>.html` (English). Search the repository for a few words of it.
2. Change it in both languages. Do not edit the built pages (`Website Code/*.html`, `Website Code/en/*.html`) or anything in `WordPress/pm-site/`: they are generated.
3. Rebuild (needs Python 3 and, for the plugin, PHP): from `Website Code/` run
   - `python3 _build/build.py` — rebuilds all pages
   - `python3 _build/wordpress.py` — rebuilds the static pages and `WordPress/pm-site.zip`, checks the PHP and runs about 180 tests
4. Upload the new `WordPress/pm-site.zip` as described in `RELEASES.md` (staging first), and add a row to the log there.

Shared parts (menu, footer, logo strip, pricing cards, closing call-to-action) are in `_build/build.py`.

## Things to know

- All buttons are `mailto:` links to `info@processmining.nu` (placeholder) until a form is added.
- Process counts and percentages on the page are illustrative demo data.
- Five of the eight system logos are placeholders; replace the files in `Website Code/assets/logos/` with the official ones (same filenames).
- The privacy page still needs the company details and a legal check.
