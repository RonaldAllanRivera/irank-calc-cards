# IRANK Calc & Cards

Lightweight WordPress plugin that adds two Gutenberg blocks without any build tools or external JS libraries.

- Weight Loss Calculator with same‑page lead form overlay
- Swipeable Product Cards using CSS scroll‑snap (no dependencies)

Tested on WordPress 6.8.3+ and PHP 8.4. Works on shared PHP‑only hosting and Laragon. No Node, React, or Vue required.

## Plugin metadata
- Requires at least: 6.8
- Requires PHP: 8.1
- Stable tag: 0.1.4
- License: GPL-2.0-or-later

## Description
Server‑rendered (dynamic) blocks with progressive enhancement. The calculator includes a draggable range slider, responsive before/after image reveal, animated loss estimate, and a same‑page lead form overlay. Product cards are swipeable with CSS scroll‑snap and small JS helpers.

## Features
- Dynamic blocks (SSR) with tiny vanilla JS enhancement
- Editor controls for all calculator text:
  - Headline, weight labels, Before/After labels
  - CTA text and timer text in "Text Labels" panel
  - Ranges, loss factor, unit, colors, images
- Typography controls (Typography panel): font family, weight, size, color per text
  - Fonts: Poppins (bundled via Google Fonts), Nohemi (fallback to Poppins/system)
- Buttons panel for CTA and Before/After label colors
  - CTA: background/text + hover background/text/border
  - Labels: background/text + hover background/text/border
- CTA button defaults: 70% width (centered), 64px radius; labels 64px radius
- Before/After labels are clickable to reveal left/right and auto‑hide near edges
- Responsive before/after images with `srcset` when media IDs are used
- Lead form overlay opens on CTA click (Escape closes)

## Installation
1. Copy `irank-calc-cards` to `wp-content/plugins/`.
2. Activate in WP Admin → Plugins.
3. Go to Settings → IRANK Calc & Cards to adjust defaults.

## Usage
### Blocks
- Weight Loss Calculator: add images (Before/After), set min/max/step, initial weight, factor, unit, colors, CTA text.
- Product Cards: manage cards via the Inspector repeater (name, tagline, price, benefits, badge, CTA).

### Lead form overlay
- The calculator CTA opens a modal overlay with a simple lead form (Full name, Email, Phone). Email is validated client‑side. No server‑side submission is performed.

## Settings
Admin → Settings → IRANK Calc & Cards
- Min/Max/Step, Loss factor, Unit (lbs/kg)
- Gradient colors
- Nohemi CSS URL (optional) — paste your cloud CSS for Nohemi (Adobe Fonts/Typekit or your CDN)

## Typography & Fonts
- Typography panel in the Calculator block lets you set font family (Poppins or Nohemi), weight (500/600/700), size (px), and color per label/CTA/timer.
- Poppins is loaded from Google Fonts for frontend and editor.
- Nohemi is not bundled; to use it, provide your cloud CSS URL in Settings → IRANK Calc & Cards. Otherwise it falls back to Poppins/system fonts.

## Privacy
- No server‑side lead submission or tracking is performed by default.

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
