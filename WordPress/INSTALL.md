# PM Site plugin — processmining.nu on WordPress

`pm-site.zip` puts the processmining.nu design (Dutch and English) on WordPress as page
templates. It does not change the theme or any other plugin. Everything it does can be undone
with one button, and deactivating the plugin removes it entirely.

> **Who uploads:** you do. Claude builds and tests the `.zip` and says what to check; the upload
> in wp-admin is a human step. See `RELEASES.md` for the routine and the log of uploaded versions.

Never edit the plugin files by hand; they are generated from the static site:

```bash
cd "Website Code" && python3 _build/wordpress.py
```

The build rebuilds the static site, converts every page to a template, checks every internal
link, syntax-checks the PHP and runs a smoke test against a small stand-in for WordPress
(176 checks). A failing check stops the build.

## Needs

- WordPress 6.0 or newer, PHP 7.4 or newer. Any theme (the plugin keeps the theme's CSS and
  JS off its own pages).
- **Pretty permalinks** (Instellingen → Permalinks → *Berichtnaam*), because the English pages
  live under `/en/`. The setup screen warns when this is not set.
- Yoast SEO is optional: when it is active the titles and descriptions are written into it.

## Install or update

**Plugins → Plugin toevoegen → Plugin uploaden** → `pm-site.zip` → Nu installeren.
If it is already installed, WordPress asks to **replace the current version** → confirm.
Then **Activeren**.

## Setup screen (admin menu → PM Site)

1. **Pages** — *Connect all pages*. Creates the 24 pages (12 Dutch at the root, 12 English under
   `/en/`) or reuses existing ones with the same address, sets the Dutch home as the front page
   and gives every page its template. LiteSpeed is purged.
2. **Titles and descriptions** — writes the SEO texts into Yoast. The old ones are kept.

| Page | Dutch | English |
|---|---|---|
| Home | `/` | `/en/` |
| Approach | `/aanpak/` | `/en/approach/` |
| Pricing | `/prijzen/` | `/en/pricing/` |
| Process Scan | `/scan/` | `/en/scan/` |
| Connectors | `/koppelingen/` | `/en/connectors/` |
| Finance | `/finance/` | `/en/finance/` |
| Internal audit | `/internal-audit/` | `/en/internal-audit/` |
| Accountants | `/accountants/` | `/en/accountants/` |
| Examples | `/voorbeelden/` | `/en/examples/` |
| About us | `/over-ons/` | `/en/about/` |
| Contact | `/contact/` | `/en/contact/` |
| Privacy | `/privacy/` | `/en/privacy/` |

A wrong address shows the design's 404 page (English under `/en/`). Dutch and English versions
of a page point at each other with `hreflang` links.

## Check

- [ ] Every page in the table shows "design".
- [ ] Home: the purple scroll thread draws as you scroll, the logo strip moves, the menu "Voor wie" opens.
- [ ] Language switch (NL / EN) goes to the same page in the other language.
- [ ] Phone: menu opens, no horizontal scrolling.
- [ ] A non-existing address shows the 404 page.
- [ ] In a private window (logged out = what visitors get from the cache).

## What is not in the plugin (yet)

- **Forms:** every button is a mail link to `info@processmining.nu` (placeholder address). A form
  comes later; the plugin can get form slots like the Coney one.
- **Menus and footer** are part of the design (not WordPress menus); change them in the static
  site and rebuild.
- **Sitemap and robots.txt:** WordPress/Yoast make their own; the static `sitemap.xml` is not used here.

## Undo

**PM Site → Undo**: every page goes back to its previous template, created pages go back to draft,
the front page setting comes back, the old Yoast texts come back.

## If something looks wrong

- **Sections stay invisible / no animations:** LiteSpeed Cache → Page Optimization: add `pm-site`
  to *JS Excludes*, *JS Deferred/Delayed Excludes* and *CSS Excludes*, then Purge All.
- **Still seeing the theme's header:** the page does not have the design template yet. Open
  PM Site and press *Connect all pages*.
