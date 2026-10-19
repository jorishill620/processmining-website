# processmining.nu — full website project

Process mining as a managed subscription, sold as a **Coney Solutions** service. We run the mining on Process Science + Evidant DTR; licences are included. Brand: black, white, bright purple `#8b3dff`; Montserrat + IBM Plex Sans; Dutch copy with a full English version.

```
PM.nu Full Website/
├── Brand/ ……………………………… logo PNGs (mark, horizontal for light and dark backgrounds) + mark SVG
├── Concept/CONCEPT.md ……… positioning, customers, pricing hypotheses, go-to-market, branding, scroll-thread spec, open items
└── Website Code/
    ├── *.html ………………………… built Dutch pages (do not hand-edit)
    ├── en/*.html …………………… built English pages (do not hand-edit)
    ├── _src/nl, _src/en ……… page sources: THIS is what you edit (one <main> body per page)
    ├── _build/build.py ……… builds everything: python3 _build/build.py   (run from Website Code/)
    ├── css/pm.css, js/site.js, js/thread.js, assets/ (logos, favicon)
    └── sitemap.xml, robots.txt, _headers, _redirects (generated)
```

## Pages (NL / EN)
Home · Aanpak / Approach · Prijzen / Pricing · Process Scan · Koppelingen / Connectors · Finance · Internal audit (+ risk & compliance) · Accountants · Voorbeelden / Examples · Over ons / About · Contact · Privacy · 404.
Shared header, footer, logo grid, pricing cards and closing call-to-action live in `_build/build.py` (per language). Links in sources use `{{link:<dutch-slug>}}`.
Product order everywhere: Monitor, Continuous, Scan.

## Edit and preview
1. Edit `_src/nl/<page>.html` and `_src/en/<page>.html`.
2. `python3 _build/build.py`, then open `index.html` in a browser (works from disk; no server needed).

## WordPress
The site can also run on WordPress as the **PM Site** plugin (`WordPress/pm-site.zip`, built with `python3 _build/wordpress.py`). See `WordPress/INSTALL.md` for install, setup and checks, and `RELEASES.md` for the upload routine and the version log.

## Deploy (static)
Cloudflare Pages or Netlify: publish the `Website Code/` folder as-is (no build command needed, or run the build command above). Clean URLs work automatically (`/prijzen` for `prijzen.html`). `_headers` and `_redirects` are in place. Set the domain processmining.nu and check the canonical/hreflang URLs in the sitemap.

## Open before launch
- **Lead form:** all calls to action are `mailto:` links to `info@processmining.nu` (placeholder address). Form comes later (Formspree or Netlify Forms).
- **Analytics:** none yet. Suggest a cookieless tool (e.g. Plausible); update the privacy page if added.
- **Logos:** five of eight are placeholders, see CONCEPT.md section 8.
- **Claims to confirm with the partners:** connector list and routes, "read-only", licences included, partner names.
- **Privacy page:** placeholders for company details; legal review.
- **English:** translated by Claude; have Michael (multilingual decisions) and a native reader check it.
- **All process counts and percentages are illustrative demo data** (home page thread and diagram); the footer says so.
- Style references (legencymedia.com, indisea.com) were not reachable from the build environment.
