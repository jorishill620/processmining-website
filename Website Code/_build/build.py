#!/usr/bin/env python3
"""Build processmining.nu: python3 _build/build.py   (run from "Website Code/")

Page content lives in _src/nl/*.html and _src/en/*.html (one <main> body each, with a <!--meta ...--> header).
Header, footer, logos, pricing cards and the closing call-to-action are shared and defined below, per language.
Output: NL pages in the folder root, EN pages in en/, plus sitemap.xml, robots.txt, _headers, _redirects.
"""
import os, re, sys, html

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
os.chdir(ROOT)
BASE = "https://processmining.nu/"
EMAIL = "info@processmining.nu"

# (nl slug, en slug)
PAGES = [
    ("index", "index"), ("aanpak", "approach"), ("prijzen", "pricing"), ("scan", "scan"),
    ("koppelingen", "connectors"), ("finance", "finance"), ("internal-audit", "internal-audit"),
    ("accountants", "accountants"), ("voorbeelden", "examples"), ("over-ons", "about"),
    ("contact", "contact"), ("privacy", "privacy"), ("404", "404"),
]
EN_OF = {nl: en for nl, en in PAGES}
NL_OF = {en: nl for nl, en in PAGES}

LOGOS = [("sap", "SAP"), ("oracle", "Oracle"), ("exact", "Exact"), ("microsoft-dynamics-365", "Microsoft Dynamics 365"),
         ("salesforce", "Salesforce"), ("servicenow", "ServiceNow"), ("afas", "AFAS"), ("workday", "Workday")]

T = {
 "nl": {
  "html_lang": "nl", "locale": "nl_NL", "suffix": " | processmining.nu", "other": "EN", "other_name": "English",
  "skip": "Naar de inhoud",
  "nav": {"approach": "Aanpak", "pricing": "Prijzen", "connectors": "Koppelingen", "who": "Voor wie", "about": "Over ons", "cta": "Plan een gesprek"},
  "who": [("finance", "Finance en shared service centers", "Inkoop, verkoop en administratie"),
          ("internal-audit", "Internal audit", "Volledige populatie, continue controles"),
          ("internal-audit#compliance", "Risk en compliance", "Beleid, controles en bewijs"),
          ("accountants", "Accountants en adviseurs", "Partner- en white-labelmodel")],
  "logos_label": "Wij lezen de data uit jouw systemen",
  "logos_more_t": "Staat jouw systeem er niet bij?", "logos_more_a": "Vraag het ons",
  "foot_tag": "Process mining als abonnement. Wij doen het werk, jij krijgt de inzichten.",
  "foot_cols": [
    ("Dienst", [("aanpak", "Aanpak"), ("prijzen", "Prijzen"), ("scan", "Process Scan"), ("koppelingen", "Koppelingen")]),
    ("Voor wie", [("finance", "Finance"), ("internal-audit", "Internal audit"), ("accountants", "Accountants")]),
    ("Bedrijf", [("over-ons", "Over ons"), ("voorbeelden", "Voorbeelden"), ("contact", "Contact"), ("privacy", "Privacy")]),
  ],
  "foot_contact": "Contact",
  "legal": "&copy; 2026 processmining.nu, een dienst van Coney Solutions",
  "legal2": "Procesvisualisaties zijn illustratief",
  "cta_h": "Zie wat je proces echt doet.",
  "cta_p": "Stuur ons een voorbeeldextract en je krijgt binnen een week drie afwijkingen terug, gratis en zonder verplichtingen.",
  "cta_b1": "Vraag je gratis X-ray aan", "cta_b2": "Plan een kennismaking", "cta_s1": "Gratis%20X-ray", "cta_s2": "Kennismaking",
  "cases": "cases",
  "plans": [
    {"n": "Monitor", "pop": "Meest gekozen", "for": "Eén proces blijvend onder controle, met maandelijkse acties.", "amt": "v.a. € 2.250", "per": " / maand",
     "one": "+ eenmalige onboarding, looptijd 12 maanden",
     "f": ["Maandelijkse data-verversing", "Live dashboard", "Maandelijkse inzichtsessie", "Kwartaalplan voor verbetering", "Licenties inbegrepen"],
     "b": "Plan een gesprek", "href": "contact"},
    {"n": "Continuous", "for": "Meerdere processen en doorlopende controles voor audit en GRC.", "amt": "v.a. € 5.500", "per": " / maand",
     "one": "tot 3 processen, + onboarding",
     "f": ["Alles uit Monitor", "Continue controlemonitoring", "Extra analistuuren op afroep", "Actietracking", "Licenties inbegrepen"],
     "b": "Vraag een voorstel", "href": "contact"},
    {"n": "Scan", "for": "Ontdek in vier weken wat er in één proces speelt.", "amt": "v.a. € 7.500", "per": "",
     "one": "eenmalig, verrekend bij een abonnement",
     "f": ["Eén proces, één data-extractie", "Analyse en rapport", "Bevindingen-workshop", "Verrekend binnen 60 dagen"],
     "b": "Bekijk de scan", "href": "scan"},
  ],
 },
 "en": {
  "html_lang": "en", "locale": "en_GB", "suffix": " | processmining.nu", "other": "NL", "other_name": "Nederlands",
  "skip": "Skip to content",
  "nav": {"approach": "Approach", "pricing": "Pricing", "connectors": "Connectors", "who": "Who it's for", "about": "About us", "cta": "Book a call"},
  "who": [("finance", "Finance and shared service centres", "Purchasing, sales and administration"),
          ("internal-audit", "Internal audit", "Full population, continuous controls"),
          ("internal-audit#compliance", "Risk and compliance", "Policy, controls and evidence"),
          ("accountants", "Accountants and advisers", "Partner and white-label model")],
  "logos_label": "We read the data straight from your systems",
  "logos_more_t": "Is your system not listed?", "logos_more_a": "Ask us",
  "foot_tag": "Process mining as a subscription. We do the work, you get the insights.",
  "foot_cols": [
    ("Service", [("aanpak", "Approach"), ("prijzen", "Pricing"), ("scan", "Process Scan"), ("koppelingen", "Connectors")]),
    ("Who it's for", [("finance", "Finance"), ("internal-audit", "Internal audit"), ("accountants", "Accountants")]),
    ("Company", [("over-ons", "About us"), ("voorbeelden", "Examples"), ("contact", "Contact"), ("privacy", "Privacy")]),
  ],
  "foot_contact": "Contact",
  "legal": "&copy; 2026 processmining.nu, a service of Coney Solutions",
  "legal2": "Process visuals are illustrative",
  "cta_h": "See what your process really does.",
  "cta_p": "Send us a sample extract and within a week you get three deviations back, free of charge and without obligation.",
  "cta_b1": "Request your free X-ray", "cta_b2": "Book an introduction", "cta_s1": "Free%20X-ray", "cta_s2": "Introduction",
  "cases": "cases",
  "plans": [
    {"n": "Monitor", "pop": "Most popular", "for": "One process kept under control, with monthly actions.", "amt": "from € 2,250", "per": " / month",
     "one": "+ one-off onboarding, 12-month term",
     "f": ["Monthly data refresh", "Live dashboard", "Monthly insight session", "Quarterly improvement plan", "Licences included"],
     "b": "Book a call", "href": "contact"},
    {"n": "Continuous", "for": "Several processes and ongoing controls for audit and GRC.", "amt": "from € 5,500", "per": " / month",
     "one": "up to 3 processes, + onboarding",
     "f": ["Everything in Monitor", "Continuous controls monitoring", "Extra analyst hours on demand", "Action tracking", "Licences included"],
     "b": "Request a proposal", "href": "contact"},
    {"n": "Scan", "for": "Find out in four weeks what is going on in one process.", "amt": "from € 7,500", "per": "",
     "one": "one-off, credited against a subscription",
     "f": ["One process, one data extraction", "Analysis and report", "Findings workshop", "Credited within 60 days"],
     "b": "See the scan", "href": "scan"},
  ],
 },
}

SPRITE = """<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
<symbol id="i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
<symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
<symbol id="i-chev" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
<symbol id="i-db" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></symbol>
<symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></symbol>
<symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/></symbol>
<symbol id="i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-6 7-6s7 2.5 7 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M22 20c0-2.8-1.8-4.8-4.5-5.6"/></symbol>
<symbol id="i-euro" viewBox="0 0 24 24"><path d="M18 6.5A7 7 0 1 0 18 17.5M4 10h10M4 14h10"/></symbol>
<symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></symbol>
<symbol id="i-layers" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/></symbol>
<symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/></symbol>
<symbol id="i-lock" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></symbol>
<symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
<symbol id="i-mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></symbol>
<symbol id="i-file" viewBox="0 0 24 24"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></symbol>
<symbol id="i-refresh" viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-15-6.7L3 8M3 3v5h5M3 12a9 9 0 0 0 15 6.7L21 16M21 21v-5h-5"/></symbol>
<symbol id="i-handshake" viewBox="0 0 24 24"><path d="M3 12l5-5 4 3 4-3 5 5-8 8z"/></symbol>
</defs></svg>"""


def meta_of(text):
    m = re.match(r"\s*<!--meta\n(.*?)-->\n?", text, re.S)
    meta = {}
    if m:
        for line in m.group(1).splitlines():
            if ":" in line:
                k, v = line.split(":", 1)
                meta[k.strip()] = v.strip()
        text = text[m.end():]
    return meta, text


def href(lang, slug_nl, root):
    """link from a page in `lang` to the page with Dutch slug / anchor"""
    frag = ""
    if "#" in slug_nl:
        slug_nl, frag = slug_nl.split("#", 1)
        frag = "#" + frag
    s = slug_nl if lang == "nl" else EN_OF[slug_nl]
    return f"{s}.html{frag}"


def logo_img(slug, name, cls="lg"):
    return f'<img class="{cls}" src="{{{{root}}}}assets/logos/{slug}.svg" alt="{name}" loading="lazy">'


def sc_logo_marquee(lang):
    t = T[lang]
    imgs = "".join(f'<img src="{{{{root}}}}assets/logos/{s}.svg" alt="">' for s, _ in LOGOS) * 2
    return (f'<div class="marq" aria-label="{t["logos_label"]}">\n  <p class="marq__lbl">{t["logos_label"]}</p>\n'
            f'  <div class="marq__view"><div class="marq__track" aria-hidden="true">{imgs}</div></div>\n</div>')


def sc_logo_grid(lang):
    t = T[lang]
    cards = "".join(f'<div class="lcard rv">{logo_img(s, n, "")}' + (f'<small>{n}</small>' if s in ("oracle", "salesforce") else "") + '</div>' for s, n in LOGOS)
    more = f'<a class="lcard lcard--more rv" href="{href(lang, "contact", "")}">{t["logos_more_t"]} {t["logos_more_a"]} &rarr;</a>'
    return f'<div class="logos">{cards}{more}</div>'


def sc_plans(lang):
    out = []
    for p in T[lang]["plans"]:
        feats = "".join(f'<li><svg class="glyph"><use href="#i-check"/></svg>{f}</li>' for f in p["f"])
        tag = f'<span class="tag">{p["pop"]}</span>' if p.get("pop") else ""
        cls = "plan pop rv" if p.get("pop") else "plan rv"
        btn = "btn btn--white" if p.get("pop") else "btn btn--ghost"
        per = f' <small>{p["per"].strip()}</small>' if p["per"] else ""
        out.append(f'<div class="{cls}">{tag}<h3>{p["n"]}</h3><p class="for">{p["for"]}</p>'
                   f'<div class="amt">{p["amt"]}{per}</div><p class="one">{p["one"]}</p><ul>{feats}</ul>'
                   f'<a class="{btn}" href="{href(lang, p["href"], "")}">{p["b"]}</a></div>')
    return '<div class="price">' + "".join(out) + "</div>"


def sc_cta(lang, end=False):
    t = T[lang]
    attr = " data-end" if end else ""
    return (f'<section class="cta" id="contact"{attr}>\n  <div class="wrap">\n    <h2 class="rv">{t["cta_h"]}</h2>\n    <p class="rv">{t["cta_p"]}</p>\n'
            f'    <div class="btn-row rv">\n      <a class="btn btn--dark" href="mailto:{EMAIL}?subject={t["cta_s1"]}">{t["cta_b1"]} <svg class="glyph"><use href="#i-arrow"/></svg></a>\n'
            f'      <a class="btn btn--outline" href="mailto:{EMAIL}?subject={t["cta_s2"]}">{t["cta_b2"]}</a>\n    </div>\n  </div>\n</section>')


def header(lang, slug_nl, nav_key):
    t = T[lang]; n = t["nav"]
    cur = lambda k: ' aria-current="page"' if nav_key == k else ""
    who = "".join(f'<a href="{href(lang, s, "")}">{a}<small>{b}</small></a>' for s, a, b in t["who"])
    other_href = (f"en/{EN_OF[slug_nl]}.html" if lang == "nl" else f"../{slug_nl}.html")
    other_lang = "en" if lang == "nl" else "nl"
    lang_a = f'<a class="lang" href="{other_href}" hreflang="{other_lang}" lang="{other_lang}" title="{t["other_name"]}">{t["other"]}</a>'
    contact = href(lang, "contact", "")
    whoc = ' aria-current="true"' if nav_key == "who" else ""
    return f'''<a class="skip" href="#top">{t["skip"]}</a>
<header class="nav" id="nav">
  <div class="wrap nav__in">
    <a class="logo" href="{href(lang, "index", "")}" aria-label="processmining.nu">
      <svg viewBox="0 0 32 32" aria-hidden="true"><rect width="32" height="32" rx="9" fill="#8b3dff"/><path d="M9 16L23 9M9 16L23 23" stroke="#fff" stroke-width="2"/><circle cx="9" cy="16" r="3" fill="#fff"/><circle cx="23" cy="9" r="3" fill="#fff"/><circle cx="23" cy="23" r="3" fill="#fff"/></svg>
      <span>processmining<b>.nu</b></span>
    </a>
    <nav class="menu" aria-label="Menu">
      <a href="{href(lang, "aanpak", "")}"{cur("approach")}>{n["approach"]}</a>
      <a href="{href(lang, "prijzen", "")}"{cur("pricing")}>{n["pricing"]}</a>
      <a href="{href(lang, "koppelingen", "")}"{cur("connectors")}>{n["connectors"]}</a>
      <div class="dd"><button class="dd__btn" type="button" aria-expanded="false" aria-haspopup="true"{whoc}>{n["who"]} <svg class="glyph"><use href="#i-chev"/></svg></button><div class="dd__menu">{who}</div></div>
      <a href="{href(lang, "over-ons", "")}"{cur("about")}>{n["about"]}</a>
      <div class="menu__m">{lang_a}<a class="btn" href="{contact}">{n["cta"]}</a></div>
    </nav>
    <div class="nav__r">{lang_a}<a class="btn" href="{contact}">{n["cta"]}</a></div>
    <button class="burger" id="burger" aria-label="Menu" aria-expanded="false"><svg class="glyph" viewBox="0 0 24 24" style="width:28px;height:28px"><path d="M3 7h18M3 12h18M3 17h18"/></svg></button>
  </div>
</header>'''


def footer(lang):
    t = T[lang]
    cols = "".join(
        f'<div><h4>{h}</h4><ul>' + "".join(f'<li><a href="{href(lang, s, "")}">{l}</a></li>' for s, l in items) + "</ul></div>"
        for h, items in t["foot_cols"])
    return f'''<footer>
  <div class="wrap">
    <div class="foot">
      <div>
        <a class="logo" href="{href(lang, "index", "")}"><span>processmining<b>.nu</b></span></a>
        <p>{t["foot_tag"]}</p>
        <p><a href="mailto:{EMAIL}">{EMAIL}</a></p>
      </div>
      {cols}
    </div>
    <div class="legal"><span>{t["legal"]}</span><span>{t["legal2"]}</span></div>
  </div>
</footer>'''


def build_page(lang, slug_nl, slug_en):
    t = T[lang]
    slug = slug_nl if lang == "nl" else slug_en
    src = f"_src/{lang}/{slug}.html"
    if not os.path.exists(src):
        print("MISSING", src); return None
    meta, body = meta_of(open(src, encoding="utf-8").read())
    root = "" if lang == "nl" else "../"
    title = meta.get("title", slug) + t["suffix"]
    desc = meta.get("desc", "")
    # shortcodes
    body = body.replace("{{logo_marquee}}", sc_logo_marquee(lang)).replace("{{logo_grid}}", sc_logo_grid(lang))
    body = body.replace("{{plans}}", sc_plans(lang)).replace("{{cta_end}}", sc_cta(lang, True)).replace("{{cta}}", sc_cta(lang))
    body = re.sub(r"\{\{link:([a-z0-9#-]+)\}\}", lambda m: href(lang, m.group(1), ""), body)
    # alternate languages
    nl_url = BASE + ("" if slug_nl == "index" else slug_nl)
    en_url = BASE + "en/" + ("" if slug_en == "index" else slug_en)
    own = nl_url if lang == "nl" else en_url
    robots = '<meta name="robots" content="noindex">\n' if slug_nl == "404" else ""
    alt = "" if slug_nl == "404" else (f'<link rel="alternate" hreflang="nl" href="{nl_url}">\n<link rel="alternate" hreflang="en" href="{en_url}">\n'
                                       f'<link rel="alternate" hreflang="x-default" href="{nl_url}">\n<link rel="canonical" href="{own}">\n')
    main_attrs = " " + meta["main"] if meta.get("main") else ""
    scripts = '<script src="{{root}}js/site.js" defer></script>'
    if meta.get("thread"):
        scripts += '\n<script src="{{root}}js/thread.js" defer></script>'
    page = f'''<!doctype html>
<html lang="{t["html_lang"]}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{html.escape(title, quote=False)}</title>
<meta name="description" content="{html.escape(desc)}">
{robots}{alt}<meta property="og:type" content="website">
<meta property="og:title" content="{html.escape(title)}">
<meta property="og:description" content="{html.escape(desc)}">
<meta property="og:locale" content="{t["locale"]}">
<meta name="theme-color" content="#0a0a0f">
<link rel="icon" href="{{{{root}}}}assets/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{{{root}}}}css/pm.css">
</head>
<body>
{SPRITE}
{header(lang, slug_nl, meta.get("nav", ""))}
<main id="top"{main_attrs}>
{body.strip()}
</main>
{footer(lang)}
{scripts}
</body>
</html>
'''
    page = page.replace("{{root}}", root)
    out = f"{slug}.html" if lang == "nl" else f"en/{slug}.html"
    os.makedirs(os.path.dirname(out) or ".", exist_ok=True)
    open(out, "w", encoding="utf-8").write(page)
    return (nl_url, en_url, slug_nl != "404")


def main():
    os.makedirs("en", exist_ok=True)
    urls = []
    for nl, en in PAGES:
        a = build_page("nl", nl, en)
        b = build_page("en", nl, en)
        if a and a[2]:
            urls.append((a[0], a[1]))
    sm = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">']
    for nlu, enu in urls:
        for u in (nlu, enu):
            sm.append(f'  <url><loc>{u}</loc><xhtml:link rel="alternate" hreflang="nl" href="{nlu}"/><xhtml:link rel="alternate" hreflang="en" href="{enu}"/></url>')
    sm.append("</urlset>")
    open("sitemap.xml", "w").write("\n".join(sm) + "\n")
    open("robots.txt", "w").write(f"User-agent: *\nAllow: /\n\nSitemap: {BASE}sitemap.xml\n")
    open("_headers", "w").write("/*\n  X-Content-Type-Options: nosniff\n  Referrer-Policy: strict-origin-when-cross-origin\n  X-Frame-Options: SAMEORIGIN\n  Permissions-Policy: camera=(), microphone=(), geolocation=()\n\n/css/*\n  Cache-Control: public, max-age=3600\n/js/*\n  Cache-Control: public, max-age=3600\n/assets/*\n  Cache-Control: public, max-age=86400\n")
    open("_redirects", "w").write("# Cloudflare Pages / Netlify. Clean URLs (/prijzen) work automatically for prijzen.html.\n# Add redirects from old addresses here.\n")
    open("assets/favicon.svg", "w").write('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" rx="9" fill="#8b3dff"/><path d="M9 16L23 9M9 16L23 23" stroke="#fff" stroke-width="2"/><circle cx="9" cy="16" r="3" fill="#fff"/><circle cx="23" cy="9" r="3" fill="#fff"/><circle cx="23" cy="23" r="3" fill="#fff"/></svg>')
    print("built", len(urls) * 2 + 2, "pages")


if __name__ == "__main__":
    main()
