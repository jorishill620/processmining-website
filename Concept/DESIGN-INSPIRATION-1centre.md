# Design inspiration: 1centre.com — analysis and 20 improvements

Status: concept for review, 23 Nov 2026. Reference: https://www.1centre.com/ (a trade-credit software company). Analysed in a headless browser at 1440px, whole page scrolled (18,700px), every section captured, CSS and JS read. Desktop only; mobile behaviour not checked.

**Take the ideas, not the assets.** The dot-matrix lettering, logo particles and layouts belong to 1Centre. Everything here is rebuilt in our own purple and process-flow language.

## How their site is built
Next.js + Tailwind, 51 CSS keyframe animations, 12 2D canvases, scroll reveals with IntersectionObserver. No WebGL or 3D library found. About 1.7 MB of JavaScript (we stay far lighter). 25 `prefers-reduced-motion` rules.

## Their visual style
- Colours: warm near-black `#0b0b0a`, warm paper `#f6f5f1` (not pure white), one cool accent (sky blue `#4ab0ff`), violet `#6d5df5`, success green `#16a37a`, deep navy `#061426` for one chapter.
- Type: Inter weight 500, tight tracking (−2px at 56px), two-tone headlines (white, then grey).
- Details: rounded cards with hairline borders, small uppercase eyebrow labels, sections alternating dark / light / navy, content max width 1400px.

## Their interactive and animated components
1. **Hero canvas:** dot-matrix "1" logo made of particles, light rays converging into a lens flare next to the headline.
2. **Header:** switches from transparent-on-dark to light-with-border depending on the section beneath it.
3. **Floating bottom pill:** persistent "Contact sales / Book a demo" with a spinning dot-matrix logo.
4. **Counters:** $25.6B, 414k+, 4.9★ count up from 0.
5. **Logo marquee:** 28 to 52 s linear loop, faded edges.
6. **Product windows:** fake app windows (breadcrumb, tabs, table) with animated states ("Verifying… → Verified", IF/AND/THEN rule builder, "Sending…").
7. **Mini diagrams in cards:** Apply → Verify → Decide pills, hub-and-spoke of bureaux, time-to-decision bars.
8. **Chapter headings:** "Open. Decide. Watch. Prove." drawn as halftone dots on canvas, a few blue dots.
9. **Dot-matrix map** of Australia and New Zealand with city pings.
10. **Alert panel:** tabs, list, detail pane, a fake cursor that clicks "Actioned" with a ripple.
11. **ID-check demo:** scan line over a licence, face match with progress bar, ticking checklist.
12. **Audit-trail timeline:** vertical steps that tick in ("Tamper-evident").
13. **Proof section:** horizontal lines of varying length with a blue sweep, plus stats.
14. **Buttons:** drifting dot texture on pill buttons, a blue "candy" variant.
15. **Closing:** FAQ accordion (12 questions), gradient glow with dot grid, five-column footer.

## 20 improvements for processmining.nu
★ = first batch. Effort: S / M / L. Status column: **built** = on branch `design-1centre`, **not built** = left for a later round.

| # | Improvement | Reference | For us | Effort | Status |
|---|---|---|---|---|---|
| 1 ★ | **Signature hero canvas** | Particles converge into a "focus" | Event-log dots enter as chaos, line up along process lanes and converge into the ring where the scroll thread begins. Pauses off-screen; reduced-motion shows a still frame. | M | built |
| 2 | Calmer headline | Weight 500, tight tracking, two-tone | Montserrat 600 instead of 800, second line in grey | S | not built |
| 3 | Animated, honest counters | Count-up from 0 once | Count-up for the stats, but only with real figures | S | not built |
| 4 ★ | **Product in a real-looking window** | App windows with states | The flow diagram inside a window frame, with a variants table that fills in and a highlighted "afwijking" row | M | built |
| 5 | Cursor-and-click demo | Fake cursor + ripple | Cursor clicks the deviation row, detail strip opens; loops | M | built (inside #4) |
| 6 | Case timeline | Ticking audit trail | One invoice's life as a timeline (Voorbeelden, Finance) | M | not built |
| 7 ★ | **Mini diagrams in the "what you get" cards** | Tag + heading + grey line + live mini diagram fading out | Each card gets a small animated diagram (source → event log, variants, analyst session, controls scan) | M | built |
| 8 | Interactive controls demo | Clickable alert list with detail pane | "Controles" demo for the audit audience | L | not built |
| 9 | Connector hub | Logo in the middle, bureaux on dotted lines | SAP, Oracle, Exact … around the PM.nu mark with data pulses | M | not built |
| 10 ★ | **Chapters with giant one-word headings** | "Open. Decide. Watch. Prove." in halftone | "Ophalen. Analyseren. Handelen. Volgen." (EN: Connect. Analyse. Act. Monitor.), alternating dark and light sections | M | built |
| 11 ★ | **Header follows the section** | Header changes with the background | Header turns light over light sections | S | built |
| 12 | Floating call-to-action pill | Persistent bar after the hero | Pill with the logo mark and "Gratis X-ray" / "Plan een gesprek" | S | built, then **removed on request** (Nov 2026) |
| 13 | Marquee polish | Slow loop, faded edges | Pause on hover, second row in the opposite direction | S | not built |
| 14 | A small set of ambient effects | Drifting dot texture, scan line, ping rings, 14px rise-in | Pick three or four and use them everywhere; reveal 28px → 14px | S | partly (rise-in, scan, ping used in the new parts) |
| 15 | Motion discipline | 25 reduced-motion rules | Reduced-motion for every new effect; pause canvases and loops off-screen | S | built (all new effects) |
| 16 | Auto-advancing product tour | Progress-bar keyframe for step-through demos | One window with tabs that advances every 6 s | M | not built |
| 17 | Real people and photos | Customer photo with "LIVE" badge and overlaid UI card | Two or three real photos of finance teams (needs assets) | needs assets | not built |
| 18 ★ | **Proof section from our own visual** | "Don't take our word for it" with animated lines and big stats | Lines for process variants, happy path thickest, purple pulse sweeping through. Anonymous quotes only once real and approved. | M | built (lines + illustrative stats; no quotes) |
| 19 | Bigger FAQ + structured data | 12 specific questions | More questions on security, data location, integration, pricing, implementation; `FAQPage` markup | S | not built |
| 20 | Closing call-to-action and footer | Glow + dot grid, specific promise, five-column footer | Specific promise ("30 minuten, 1 proces") over a glow; fuller footer | S | not built |

## What changes on the home page (first batch)
- New story order: Probleem → Werkwijze → **Ophalen.** (Koppelingen) → **Analyseren.** (Wat je krijgt) → **Handelen.** (Voor wie) → **Volgen.** (Prijzen) → Proof (varianten) → FAQ → call-to-action. Pricing moves down, after "Voor wie"; backgrounds alternate light and dark.
- The "Wat je krijgt" section turns dark, with mini diagrams in the cards.
- The flow diagram in "Het probleem" becomes a product window with a cursor demo.
- Sub-pages get the header behaviour; their layout is unchanged. (The floating pill was built and then removed on request.)

## Review checklist
- [ ] Is the hero canvas calm enough next to the headline (not distracting)?
- [ ] Does the new order of the home page read well, especially pricing moved down?
- [ ] Are the chapter words the right words in Dutch and English?
- [ ] Proof section: the numbers are illustrative demo data and labelled as such. Keep, or wait for real figures?
- [ ] Which of the not-built items should come next? Suggestion: 6, 9, 16.

## Notes
- All demo data (counts, percentages, durations) is illustrative and labelled "voorbeeld" on the page.
- Everything stays lightweight: plain JS and CSS, canvases only run while visible, no framework.
- Photos (#17) and real quotes (#18) need real material from you; nothing is invented.
