#!/usr/bin/env python3
"""
Turns the static site into a WordPress plugin, "PM Site", that adds the design
(Dutch and English) as page templates plus a setup screen (admin menu → PM Site).
The static site stays the single source: change it, run this, upload the zip.

    python3 _build/wordpress.py          (run from "Website Code/")

Output (in the repository's WordPress/ folder, next to "Website Code"):
    WordPress/pm-site/       the plugin, readable
    WordPress/pm-site.zip    upload this: Plugins → Plugin toevoegen → Plugin uploaden

Plugin sources (PHP that is not generated) live in _build/wordpress/.
Generated here: templates/*.php (one per page and language) and includes/data.php
(templates, pages, SEO texts).

URLs: Dutch pages at the root (/aanpak/), English under /en/ (/en/approach/), like the
static site. The English pages are children of the page "en".
"""
import html as htmllib, importlib.util, os, re, shutil, subprocess, sys, time, zipfile

HERE = os.path.dirname(os.path.abspath(__file__))
SITE = os.path.dirname(HERE)                                    # Website Code
SOURCES = os.path.join(HERE, "wordpress")                       # PHP sources
OUT_ROOT = os.path.join(os.path.dirname(SITE), "WordPress")     # <repo>/WordPress
PLUGIN = "pm-site"
OUT = os.path.join(OUT_ROOT, PLUGIN)

_spec = importlib.util.spec_from_file_location("pmbuild", os.path.join(HERE, "build.py"))
B = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(B)
PAGES = [(nl, en) for nl, en in B.PAGES if nl != "404"]         # (nl slug, en slug)
NL_OF = B.NL_OF

# WordPress page titles per page: Dutch, English.
TITLES = {
    "index": ("Home", "Home"), "aanpak": ("Aanpak", "Approach"), "prijzen": ("Prijzen", "Pricing"),
    "scan": ("Process Scan", "Process Scan"), "koppelingen": ("Koppelingen", "Connectors"),
    "finance": ("Finance", "Finance"), "internal-audit": ("Internal audit", "Internal audit"),
    "accountants": ("Accountants", "Accountants"), "voorbeelden": ("Voorbeelden", "Examples"),
    "over-ons": ("Over ons", "About us"), "contact": ("Contact", "Contact"), "privacy": ("Privacy", "Privacy"),
}
THREAD_PAGES = {"index"}      # pages that load js/thread.js (the scroll thread)
FONTS = ("https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600"
         "&family=Montserrat:wght@600;700;800&display=swap")
PHP = shutil.which("php") or "/opt/homebrew/bin/php"


def read(path):
    with open(path, encoding="utf-8") as f:
        return f.read()


def write(path, text):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        f.write(text)


def php_str(s):
    return "'" + str(s).replace("\\", "\\\\").replace("'", "\\'") + "'"


def php_value(value, depth=2):
    """Python data → PHP array literal."""
    pad = "\t" * depth
    if isinstance(value, dict):
        if not value:
            return "array()"
        rows = ",\n".join("%s%s => %s" % (pad, php_str(k), php_value(v, depth + 1)) for k, v in value.items())
        return "array(\n%s,\n%s)" % (rows, "\t" * (depth - 1))
    if isinstance(value, (list, tuple)):
        return "array( %s )" % ", ".join(php_value(v, depth + 1) for v in value) if value else "array()"
    if isinstance(value, bool):
        return "true" if value else "false"
    if isinstance(value, int):
        return str(value)
    return php_str(value)


def file_of(lang, nl_slug):
    """Template file name: nl-aanpak.php, en-approach.php, nl-home.php, nl-404.php."""
    if nl_slug == "index":
        return "%s-home.php" % lang
    slug = nl_slug if lang == "nl" else B.EN_OF[nl_slug]
    return "%s-%s.php" % (lang, slug)


def convert(path, lang, label):
    """A built page → PHP template. Returns (php, title, description)."""
    page = read(path)
    title = htmllib.unescape(re.search(r"<title>(.*?)</title>", page, re.S).group(1)).strip()
    desc_m = re.search(r'<meta name="description" content="([^"]*)"', page)
    description = htmllib.unescape(desc_m.group(1)) if desc_m else ""
    html_lang = re.search(r'<html lang="([\w-]+)"', page).group(1)

    m = re.search(r"<body([^>]*)>(.*)</body>", page, re.S)
    body = m.group(2)
    body = re.sub(r'\s*<script src="(?:\.\./)?js/[\w.-]+"[^>]*></script>', "", body)
    if "<?" in body:
        raise SystemExit("%s contains '<?', which PHP would read as code" % path)

    # Links between pages → WordPress permalinks, resolved when the page loads.
    def link(m):
        prefix, slug, frag = m.group(1), m.group(2), m.group(3) or ""
        slug_lang = "en" if (prefix == "en/" or (prefix == "" and lang == "en")) else "nl"
        key = NL_OF[slug] if slug_lang == "en" else slug
        if key not in TITLES and key != "404":
            raise SystemExit("%s links to unknown page %s" % (path, m.group(0)))
        return 'href="<?php echo esc_url( pm_site_url( %s, %s ) ); ?>%s"' % (php_str(key), php_str(slug_lang), frag)
    body = re.sub(r'href="((?:\.\./|en/)?)([\w-]+)\.html(#[\w-]+)?"', link, body)
    # Images and icons → the plugin's assets folder.
    body = re.sub(r'(src|href)="(?:\.\./)?assets/([^"]+)"',
                  lambda m: '%s="<?php echo esc_url( pm_site_asset( %s ) ); ?>"' % (m.group(1), php_str(m.group(2))), body)
    if re.search(r'(?:href|src)="(?:\.\./)?(?:css|js)/', body):
        raise SystemExit("%s still points at css/ or js/" % path)

    php = """<?php
/**
 * %(label)s
 *
 * Generated by _build/wordpress.py from %(src)s on %(stamp)s.
 * Do not edit this file: change the static site and regenerate.
 */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html lang="%(lang)s">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
%(body)s
<?php wp_footer(); ?>
</body>
</html>
""" % {"label": label, "src": os.path.relpath(path, SITE), "stamp": time.strftime("%Y-%m-%d %H:%M"),
       "lang": html_lang, "body": body.strip()}
    return php, title, description


def lint(path):
    """PHP syntax check (skipped when PHP is not installed)."""
    if not os.path.exists(PHP):
        return
    r = subprocess.run([PHP, "-l", path], capture_output=True, text=True)
    if r.returncode != 0:
        raise SystemExit("PHP syntax error in %s:\n%s" % (path, r.stdout + r.stderr))


def main():
    # Always start from a fresh static build.
    subprocess.run([sys.executable, os.path.join(HERE, "build.py")], check=True, stdout=subprocess.DEVNULL)
    if os.path.isdir(OUT):
        shutil.rmtree(OUT)
    os.makedirs(OUT)

    version = time.strftime("1.0.%Y%m%d.%H%M")
    templates, pages, seo, thread = {}, {}, {}, []
    for lang in ("nl", "en"):
        for nl_slug, en_slug in PAGES + [("404", "404")]:
            slug = nl_slug if lang == "nl" else en_slug
            src = os.path.join(SITE, "" if lang == "nl" else "en", slug + ".html")
            is404 = nl_slug == "404"
            name = file_of(lang, nl_slug)
            tkey = PLUGIN + "/" + name
            title_txt = "404" if is404 else TITLES[nl_slug][0 if lang == "nl" else 1]
            label = "PM %s – %s" % (lang.upper(), title_txt)
            php, title, description = convert(src, lang, label)
            write(os.path.join(OUT, "templates", name), php)
            seo[tkey] = {"title": title, "description": description}
            if is404:
                continue
            templates[tkey] = label
            if lang == "nl":
                path = "home" if nl_slug == "index" else nl_slug
                parent = ""
            else:
                path = "en" if nl_slug == "index" else "en/" + en_slug
                parent = "" if nl_slug == "index" else "en:index"
            pages["%s:%s" % (lang, nl_slug)] = {
                "template": tkey, "lang": lang, "slug": path.split("/")[-1], "path": path,
                "title": title_txt if lang == "nl" else title_txt, "parent": parent}
            if nl_slug in THREAD_PAGES:
                thread.append(tkey)
    print("templates: %d pages (nl + en), 2 x 404" % len(pages))

    data = {"templates": templates, "pages": pages, "seo": seo, "thread": thread, "fonts": FONTS}
    write(os.path.join(OUT, "includes", "data.php"),
          "<?php\n// Generated by _build/wordpress.py on %s. Do not edit.\n"
          "defined( 'ABSPATH' ) || exit;\n\n"
          "define( 'PM_SITE_VERSION', %s );\n\n"
          "function pm_site_data() {\n\treturn %s;\n}\n"
          % (time.strftime("%Y-%m-%d %H:%M"), php_str(version), php_value(data)))

    # Hand-written PHP, with the version number filled in.
    for root, _, files in os.walk(SOURCES):
        for name in files:
            if not name.startswith("."):
                src = os.path.join(root, name)
                write(os.path.join(OUT, os.path.relpath(src, SOURCES)), read(src).replace("{{VERSION}}", version))
    for sub in ["", "includes", "templates"]:
        write(os.path.join(OUT, sub, "index.php"), "<?php\n// Silence is golden.\n")

    checked = 0
    for root, _, files in os.walk(OUT):
        for name in files:
            if name.endswith(".php"):
                lint(os.path.join(root, name))
                checked += 1

    os.makedirs(os.path.join(OUT, "assets", "css"))
    os.makedirs(os.path.join(OUT, "assets", "js"))
    for css in ("pm.css", "wordpress.css"):
        shutil.copy(os.path.join(SITE, "css", css), os.path.join(OUT, "assets", "css", css))
    for js in ("site.js", "thread.js"):
        shutil.copy(os.path.join(SITE, "js", js), os.path.join(OUT, "assets", "js", js))
    shutil.copytree(os.path.join(SITE, "assets", "logos"), os.path.join(OUT, "assets", "logos"))

    zpath = os.path.join(OUT_ROOT, PLUGIN + ".zip")
    with zipfile.ZipFile(zpath, "w", zipfile.ZIP_DEFLATED) as z:
        for root, _, files in os.walk(OUT):
            for name in sorted(files):
                if not name.startswith("."):
                    full = os.path.join(root, name)
                    z.write(full, os.path.relpath(full, OUT_ROOT))
    tested = "not tested (PHP not installed)"
    if os.path.exists(PHP):
        r = subprocess.run([PHP, os.path.join(HERE, "wordpress-tests", "smoke.php"), OUT], capture_output=True, text=True)
        if r.returncode != 0:
            raise SystemExit("Smoke test failed:\n" + r.stdout + r.stderr)
        tested = "smoke test: " + r.stdout.strip().splitlines()[-1]
    lint_note = "%d PHP files syntax-checked, %s" % (checked, tested) if os.path.exists(PHP) else "PHP not installed, not checked"
    print("plugin    %s\n          version %s, %s" % (zpath, version, lint_note))


if __name__ == "__main__":
    main()
