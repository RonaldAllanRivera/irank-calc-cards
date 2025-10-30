# Product Cards — Technical Documentation

## Overview
This plugin provides two dynamic Gutenberg blocks. The Product Cards block renders a swipeable, server‑rendered cards carousel with a mobile viewer bar, typography controls, gradients, and accessible navigation. It requires no build tools or external JS frameworks.

- Primary file: `irank-calc-cards.php` (function `irank_cc_render_cards_block()`)
- Editor UI: `assets/js/editor.cards.js`
- Frontend assets: `assets/js/frontend.cards.js`, `assets/css/cards.css`
- Documentation: `README.md`, `HOWTO.md`, `CHANGELOG.md`, `TECHNICAL-DOCS-CALCULATOR.md`

## Architecture and Data Flow
- Server‑side rendering (SSR): Block markup is generated in PHP; attributes are sanitized and passed to the view via inline styles and CSS variables.
- Frontend enhancement: Small vanilla JS augments UX (arrow buttons, keyboard nav, mobile viewer bar state syncing).
- Styling: A scoped stylesheet (`assets/css/cards.css`) applies layout, gradients, mobile behavior, and accessibility affordances.

Render flow
1) Gutenberg saves attributes on the block instance (cards array, section texts, colors, typography).
2) PHP render callback prints the section wrapper, header texts, track container, per‑card article, and a dots container.
3) CSS controls layout (grid → flex on mobile), gradients, precise slide peek, and responsive typography.
4) JS initializes the carousel, creates dot buttons, and updates `aria-current` on the active dot while keeping prev/next state in sync.

## Feature coverage vs. original spec
- Section header + heading + subheading with typography: ✅
- Per‑card content (name, tagline, price, suffix, price tagline, benefits, badge, CTA, image): ✅
- CTA gradients (normal + hover) with CSS variables; 64px radius, 12/20 padding (desktop): ✅
- Benefits bullets with custom check icon (one per line): ✅
- Mobile UX
  - Stacked layout (content above image): ✅
  - Mobile CTA: one line, centered 90% width, 20px/12px padding, 16px font; text override to “Select this medication →”: ✅
  - Mobile viewer bar (dots) with active state styling: ✅
  - Precise slider peek using `--peek` + `--gap` variables: ✅
  - Image container sized via `--media-h-mobile` and `object-fit: cover; object-position: center`: ✅
  - Header pill fits one line at 14px; typography reductions and spacing tuned: ✅

## Why this implementation
- SSR blocks produce fast first paint, SEO‑friendly markup, and avoid build chains.
- CSS variables allow theming from PHP attributes without inline duplication of full styles.
- Minimal JS (no dependencies) provides the required carousel affordances while keeping payload tiny.

## How to edit or extend
- Block PHP
  - Renderer: `irank_cc_render_cards_block()` in `irank-calc-cards.php`.
  - Attributes: defined in `register_block_type('irank/product-cards', ...)` (section texts, card array, colors, typography, CTA gradients, badge gradients).
  - Mobile CTA text override: uses `wp_is_mobile()` to output “Select this medication →” only on mobile.
- Frontend JS
  - File: `assets/js/frontend.cards.js`.
  - Initializes track, prev/next, builds `.irank-cards__dot` buttons, sets `aria-current="true"` on the active dot, and supports keyboard left/right.
  - Helpers: `nearestIndex()`, `scrollToIndex()`, and `updateUI()`.
- Styles
  - File: `assets/css/cards.css`.
  - Key variables set on `.irank-cards`:
    - `--peek` (mobile visible next‑slide slice), `--gap` (inter‑card gap), `--media-h-mobile` (mobile media height).
    - CTA and badge gradients via `--cards-cta-*` and `--badge-*` variables.
  - Mobile media query switches `.irank-card` to flex column, applies CTA sizing, shows the viewer bar, and repositions/crops the image.

Common extensions
- Add per‑card CTA gradient overrides: add attributes, expose variables on each card wrapper, and extend CSS to prefer per‑card vars.
- Change viewer bar style: adjust `.irank-cards__dot` dimensions/colors; keep `aria-current` for accessibility.
- Add autoplay: small timer that calls `scrollToIndex(track, i+1)`; pause on interaction and respect `prefers-reduced-motion`.

## Editable content approach
- Editor attributes (Inspector) control:
  - Section texts + typography (header, heading, subheading).
  - Card fields (name, tagline, price, suffix, price tagline, benefits, badge, CTA text/url, image).
  - Colors: section gradient start/end, card background, CTA gradient start/end, CTA hover gradient start/end, CTA text + hover text/border, badge text color, badge gradient start/end.
  - Card content typography: Name, Tagline, Price, Suffix, Price Tagline, Benefits (family, weight, size, color, line-height).
- Frontend mapping
  - Typography applied inline via sanitized values and a small font family resolver (Poppins/Nohemi fallback).
  - Color inputs sanitized (e.g., `sanitize_hex_color`) and exposed as CSS variables for gradients and states.

## Accessibility and UX
- Dots are real buttons with `aria-label` and `aria-current="true"` on the active item.
- Prev/next buttons manage `aria-disabled` and keyboard focus.
- Scroll‑snap carousel still supports swipe and keyboard navigation.
- Images use `object-fit` and `object-position` to reduce layout shifting across aspect ratios.

## Security and validation
- All user‑provided strings sanitized before output.
- URLs escaped via `esc_url()`; text via `esc_html()`.
- Colors validated via `sanitize_hex_color()`.

## Trade‑offs considered
- Dots vs. arrows only: dots added for mobile discoverability; hidden on desktop per design.
- Mobile CTA override in PHP (`wp_is_mobile()`): simplest server‑side approach; if a full page cache serves the same HTML to all devices, consider a CSS/JS alternative for the label.
- Fixed mobile media height vs. responsive height: chosen variable `--media-h-mobile` for predictable cropping and quick tuning.

## Testing checklist
- Carousel scroll/keyboard/arrows work; edge disabling correct; scroll‑snap behaves on mobile.
- Viewer bar (dots) visible on mobile; active dot follows slide; buttons jump to slides.
- CTA shows mobile text, is centered at 90% width, one line, 20px/12px padding.
- Content stacks above image on mobile; image fills container with centered crop.
- Benefits show check bullets; spacing matches design.
- Section header pill stays on one line (14px) and does not clip.

## Performance notes
- No external libraries; tiny ES5 JS and a single scoped CSS file.
- Asset versioning with `filemtime()` for cache busting.
- CSS variables minimize inline style duplication and keep paint fast.

## Versioning and upgrades
- See `CHANGELOG.md` for Product Cards history (0.1.6+).
- New attributes should include sane defaults and be added to both PHP and editor JS.

## Known gaps and future ideas
- Optional autoplay with pause‑on‑hover and reduced‑motion support.
- Per‑card color theming for active dot (e.g., data‑attribute‑driven).
- Expose mobile sizing knobs (peek, gap, media height) in the editor for non‑developer users.
