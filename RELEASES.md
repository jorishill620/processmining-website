# Releases — processmining.nu WordPress plugin

Claude does not upload anything to WordPress. Claude builds and tests, then hands over a `.zip`;
**you do the upload**. Every upload is recorded here and saved in the git history, so any version
can be put back.

## Uploading a new version

1. **Plugins → Plugin toevoegen → Plugin uploaden** in wp-admin.
2. Choose `WordPress/pm-site.zip`.
3. **Nu installeren** → **Huidige vervangen door de geüploade**.
4. Open the page that changed and check it. Claude says per release what to look at.

Only when a release says *new page*: admin menu → **PM Site** → **Connect again**.

Staging first, always. Live is the same steps, done by you when staging is approved.

## Going back to an earlier version

Ask Claude for the version you want. Claude takes that `.zip` from the git history (every upload
gets a tag, `pm-<version>`) and hands it to you; you upload it the same way. The **Undo** button
on the PM Site screen puts every page back on the previous template.

## Log

Newest first. The version number is also the plugin version shown in WordPress.

| Version | Date | What changed | Uploaded to |
|---|---|---|---|
| 1.0.20261123.1025 | 23 Nov 2026 | **Design branch `design-1centre`, for review, not for upload yet.** Home page: product window demo, chapters, dark "Analyseren" section with mini diagrams, proof section, header that follows the section, footer wordmark. (The floating call-to-action pill and the hero particles were removed again on request; the scroll thread was redesigned: thinner glowing tube with a light core and a dotted route ahead, solid purple, and it plugs into cards: a branch grows out and the card lights up.) | not yet |
| 1.0.20261120.0917 | 20 Nov 2026 | Copy: data extraction wording (stat block, FAQ "Wat heb ik zelf nodig?", "Tot op de bron") in Dutch and English. Check the home page. | not yet |
| 1.0.20261019.1040 | 19 Oct 2026 | First version of the PM Site plugin: 12 pages in Dutch and English, setup screen, Yoast texts, hreflang | not yet |
