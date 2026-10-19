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
| 1.0.20261019.1040 | 19 Oct 2026 | First version of the PM Site plugin: 12 pages in Dutch and English, setup screen, Yoast texts, hreflang | not yet |
