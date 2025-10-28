# IRANK Calc & Cards

Lightweight WordPress plugin that adds two Gutenberg blocks without any build tools or external JS libraries.

- Weight Loss Calculator with same-page Results overlay and first‑party tracking
- Swipeable Product Cards using CSS scroll‑snap (no dependencies)

Tested on WordPress 6.8.3+ and PHP 8.4. Works on shared PHP‑only hosting and Laragon. No Node, React, or Vue required.

## Plugin metadata
- Requires at least: 6.8
- Requires PHP: 8.1
- Stable tag: 0.1.3
- License: GPL-2.0-or-later

## Description
Server‑rendered (dynamic) blocks with progressive enhancement. The calculator includes a draggable range slider, responsive before/after image reveal, animated loss estimate, and a same‑page results overlay. Product cards are swipeable with CSS scroll‑snap and small JS helpers.

## Features
- Dynamic blocks (SSR) with tiny vanilla JS enhancement
- Editor controls for all calculator text:
  - Headline, weight labels, Before/After labels
  - CTA text and timer text in "Text Labels" panel
  - Ranges, loss factor, unit, colors, images
- Typography controls (Typography panel): font family, weight, size, color per text
  - Fonts: Poppins (bundled via Google Fonts), Nohemi (fallback to Poppins/system)
- Responsive before/after images with `srcset` when media IDs are used
- Results overlay opens on CTA click (Escape closes)
- First‑party conversion tracking (DB + REST + `sendBeacon`), CSV export

## Installation
1. Copy `irank-calc-cards` to `wp-content/plugins/`.
2. Activate in WP Admin → Plugins.
3. Go to Settings → IRANK Calc & Cards to adjust defaults.

## Usage
### Blocks
- Weight Loss Calculator: add images (Before/After), set min/max/step, initial weight, factor, unit, colors, CTA text.
- Product Cards: manage cards via the Inspector repeater (name, tagline, price, benefits, badge, CTA).

### Results overlay
- The calculator CTA opens a modal overlay showing current weight and estimated loss. Overlay is hidden on load, toggled via `hidden` + `aria-hidden`.

## Settings
Admin → Settings → IRANK Calc & Cards
- Min/Max/Step, Loss factor, Unit (lbs/kg)
- Gradient colors
- Enable/disable tracking
- Nohemi CSS URL (optional) — paste your cloud CSS for Nohemi (Adobe Fonts/Typekit or your CDN)

## Typography & Fonts
- Typography panel in the Calculator block lets you set font family (Poppins or Nohemi), weight (500/600/700), size (px), and color per label/CTA/timer.
- Poppins is loaded from Google Fonts for frontend and editor.
- Nohemi is not bundled; to use it, provide your cloud CSS URL in Settings → IRANK Calc & Cards. Otherwise it falls back to Poppins/system fonts.

## Tracking & Privacy
- Endpoint: `POST /wp-json/irank/v1/track`
- Stored: timestamp, page ID, weight, loss, session ID, referrer, UA, anonymized IP hash
- Admin → Tools → IRANK Reports (KPIs + CSV export)

## Accessibility
- Semantic labels and `aria-live` for dynamic loss value
- Keyboard support for before/after handle and modal close (Escape)
- Respects `prefers-reduced-motion`

## Development (no‑build)
- Editor globals: `wp.blocks`, `wp.blockEditor`/`wp.editor`, `wp.components`, `wp.element`, `wp.i18n`
- Frontend: `assets/js/frontend.*.js` (vanilla ES5)
- Styles: `assets/css/frontend.css`

## Changelog
See `CHANGELOG.md` for details.
