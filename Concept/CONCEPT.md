# processmining.nu — concept

Status: updated 7 Oct 2026. Pricing and numbers are **hypotheses to validate**, not quotes.

**Decisions (7 Oct 2026, Joris):** sold as a Coney Solutions service; licences are included in the price and Conify has no role; Process Science and Evidant have shown interest in the project; product order on the site is Monitor, Continuous, Scan; no client names anywhere; the system connectors (SAP, Oracle, Exact etc.) are a key selling point and are promoted with logos; the lead form comes later.

## 1. The idea in one line
Process mining as a managed service: a fixed monthly subscription, and we do the mining. The client gets findings, a live dashboard and a monthly decision meeting, without buying licences, hiring a specialist or building a data pipeline.

## 2. Why it can work
- Most mid-market and many enterprise organisations buy a process mining licence and then stall: nobody has the time or skill to extract event logs, model them and turn them into actions.
- Coney Solutions already does this work (insourcing and co-sourcing). The subscription packages it into a repeatable product with a predictable price.
- Process Science (mining and analysis) plus Evidant DTR (extraction and transformation of ERP/system data into event logs) cover the whole chain, so we can deliver end to end.

## 3. Positioning
"Process mining, geregeld." We run it, you act on it.
- Not a tool vendor, not a long-term detacheerder: a service with a fixed price and a clear output.
- Knowledge transfer stays an option (Academy), but the default is "done for you".
- Tool-neutral framing in the copy; Process Science and Evidant are named as the engine underneath.

## 4. Target customers (ICP)
Primary: Dutch organisations of roughly 300–5,000 FTE running an ERP/workflow system, with no process mining team.
1. **Finance & shared service centres** — purchase-to-pay, order-to-cash, record-to-report. Clearest ROI (duplicate payments, late payments, rework, touchless rate).
2. **Internal audit & risk/compliance** — full-population testing instead of samples, continuous controls monitoring. Strong link with data-driven GRC.
3. **Accountancy and advisory firms** — resell or white-label the service to their own clients as a new advisory line.
4. **Secondary:** insurance and logistics operations (claims, order handling), ServiceNow/Salesforce-heavy service desks.

Buyer: CFO, Head of Finance/Controller, Head of Internal Audit, Process owner. Typical trigger: audit finding, ERP migration, cost programme, failed in-house pilot.

## 5. Offer and pricing model
Pricing per **process per year** (not per user): it matches the value, and the client can't be surprised by seat growth.

| Package (site order) | What | Indicative price |
|---|---|---|
| **Monitor** | One process kept live: monthly data refresh, dashboard, monthly insight session, quarterly improvement plan. | €2,250–3,000 / month, 12-month term, plus onboarding €5,000 |
| **Continuous** | Up to 3 processes, controls monitoring for audit/GRC, priority analyst hours, action tracking. | €5,500–7,500 / month, plus onboarding |
| **Scan** | One process, one extraction, 4-week analysis, findings workshop and report. The entry offer and the lead generator. | one-off €7,500–12,500 (credited against a subscription if signed within 60 days) |
| **Partner / white-label** | For accountancy firms: multi-client set-up, shared branding, volume rate. | custom |

Rules of thumb
- Process Science and Evidant licences are bundled into the price; the client never sees a licence invoice. Needs checking against partner terms (resale/managed-service rights) before launch.
- Target gross margin 55–65% once 6+ clients share the data pipeline templates.
- Land with Scan, convert to Monitor; Continuous is the expansion path. Annual prepay gets a discount of about 8%.
- Extra processes = a clear add-on price. No hidden hours.

## 6. Go-to-market
1. **Free "Process X-ray"** (30 minutes, one sample extract): a quick look at a P2P or O2C sample to show 3 deviations. Gate for the Scan.
2. **LinkedIn-led content** from Joris and colleagues: short teardown posts ("wat een factuur écht doet in SAP"), monthly.
3. **Audit and finance communities:** NBA/IIA-NL/FD-type events, webinars with auditors, guest talks.
4. **Outbound to a named list** of ~150 Dutch SAP/Oracle/Exact finance organisations, with a personalised X-ray offer.
5. **Partners:** accountancy firms and ERP implementers, plus Conify's existing KNIME/Diligent/ACL relationships.
6. **Proof:** no client names are used. The site has an Examples page with typical findings, clearly labelled as scenarios and not client results. Real anonymised cases can replace them once available and approved.

KPIs for the first 12 months: 40 X-rays, 12 Scans, 6 subscriptions, ~€200k ARR.

## 7. Branding
- **Name:** processmining.nu — descriptive, exact-match domain, "nu" signals "now / immediately" in Dutch. Wordmark: `processmining` in white, `.nu` in purple.
- **Palette:** black `#0a0a0f`, white `#ffffff`, bright purple `#8b3dff` (hover `#a468ff`), soft purple tint `#efe6ff`, grey `#9a98a8`.
- **Type:** Montserrat headings (800), IBM Plex Sans body — same as the Conify/Coney family so the sites feel related.
- **Voice:** direct, short sentences, no jargon first. Dutch copy, English version after approval.
- **Imagery:** flow diagrams and event-log motifs in purple lines on black; no stock photos, no emojis; line icons only.
- **Signature element:** the purple "process thread" — a node-and-line animation in the hero that is reused as a divider.

## 8. Open items
Resolved: entity (Coney Solutions service, licences included, Conify not involved), product order, no client names, connectors promoted with logos, form later.
Still open:
1. **Partner terms:** Process Science and Evidant have shown interest; get the managed-service/resale terms and the permission to show their names and logos in writing.
2. **Connector list:** confirm with Evidant and Process Science which systems are really supported and by which route (direct connection, scheduled extract, one-off). The site currently lists SAP, Oracle, Exact, Microsoft Dynamics 365, Salesforce, ServiceNow, AFAS, Workday and says "alle systemen met tijdgestempelde gebeurtenissen".
3. **Logos:** SAP, Oracle and Salesforce are official marks from the open Simple Icons set; Exact, AFAS, Microsoft Dynamics 365, ServiceNow and Workday are neutral placeholder wordmarks. Replace them with the official files (same filename in `assets/logos/`) and check each owner's brand rules.
4. **Prices and terms:** bands, 12-month term, monthly cancellation after year 1 and the 60-day scan credit are assumptions. Check against day rates.
5. **Legal:** privacy statement has placeholders for company name, address and KvK; have it reviewed.
6. **Style references:** legencymedia.com and indisea.com could not be opened from the build environment.

## 9. Signature animation: the scroll thread
One continuous SVG path drawn by scroll, no library (plain JS + `requestAnimationFrame`, inline SVG). Implemented in `index.html`.
- **Overlay:** one absolutely positioned SVG over the page (`pointer-events:none`, `overflow:visible`), above section backgrounds, behind cards/panels (`z-index` 1 vs 2). The viewBox matches the page's pixel size.
- **Path:** orthogonal, round caps, 32px stroke on desktop (10px tablet, 6px mobile), corner radius 2x stroke snapped to 16. Vertical runs sit in the page margins; horizontal shifts sit at section boundaries (7 sections marked `data-shift`). Route is measured at runtime and rebuilt on resize, font load and layout change (ResizeObserver).
- **Draw:** `stroke-dasharray` = path length, `stroke-dashoffset` set every frame from scroll. The pen tip sits at 62% of the viewport height. Deviation from a plain `progress = (scroll - start)/(end - start)`: progress is mapped per segment, so the tip stays at the scroll line on vertical runs and each horizontal shift is drawn while the tip crosses a 160px band (otherwise the long horizontal runs make the tip lag behind).
- **Start / end:** hollow ring in the hero (scales 0.4 to 1 in 600ms once drawing starts); solid end dot with halo inside the CTA block, shown at 100%. Gradient with a hard purple-to-white switch at the top of the CTA block.
- **Accessibility:** `prefers-reduced-motion` shows the fully drawn, static line.
- **Process-map layer (our addition):** each section boundary carries a process-step node (Bestelling, Goederenontvangst, Factuur, Goedkeuring, Betaling, Controle, Afgesloten) with an illustrative case count, lit purple as the pen tip passes it. A white "case" dot with a pulse rides the pen tip. Three thin branches split off and rejoin the line: two grey rework/skip routes and one purple deviation ("goedkeuring overgeslagen"). All numbers are illustrative demo data and the footer says so. Reduced-motion shows everything lit and static.
- **Not built yet (from the reference):** logo rows that speed up with scroll velocity, photo parallax, a one-off drawn connection diagram.

### Further process-mining ideas (not built)
- Stroke thickness by case frequency on the busiest stretches.
- Event-log rows (`INV-20418 · Factuur ontvangen · 09:14`) drifting in the margins next to the "Wij halen de data op" step.
- A timeline scale beside the line that ends in "doorlooptijd 28 dagen to 9 dagen".
- Section-specific shapes: the line splits into four nodes in Werkwijze and three paths in Prijzen.
