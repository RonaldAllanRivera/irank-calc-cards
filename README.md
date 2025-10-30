# IRANK Calc & Cards

Lightweight WordPress plugin that adds two Gutenberg blocks without any build tools or external JS libraries.

- Weight Loss Calculator with same‑page lead form overlay
- Swipeable Product Cards using CSS scroll‑snap (no dependencies)

Tested on WordPress 6.8.3+ and PHP 8.4. Works on shared PHP‑only hosting and Laragon. No Node, React, or Vue required.

## Plugin metadata
- Requires at least: 6.8
- Requires PHP: 8.1
- Stable tag: 0.1.6
- License: GPL-2.0-or-later

## Description
Server‑rendered (dynamic) blocks with progressive enhancement. The calculator includes a draggable range slider, responsive before/after image reveal, animated loss estimate, and a same‑page lead form overlay. Leads are saved to the WordPress database via admin‑ajax (no REST required). Product cards are swipeable with CSS scroll‑snap and small JS helpers.

## Features
- Dynamic blocks (SSR) with tiny vanilla JS enhancement
- Weight Loss Calculator
  - Editor controls for all calculator text (headline, weight labels, Before/After labels, CTA text, timer)
  - Ranges, loss factor, unit, colors, images
  - Typography controls per text (family, weight, size, color)
  - Buttons panel for CTA and Before/After label colors (normal/hover)
  - Responsive images with `srcset` when media IDs are used
  - Lead form overlay opens on CTA click (Escape closes)
- Product Cards
  - Repeater for 3+ cards (name, tagline, price, benefits, badge, CTA, image)
  - Section controls: Header, Heading, Subheading (with Typography panel)
  - Colors panel: section gradient, card background, CTA bg/text + hover bg/text/border, badge text color, badge gradient start/end
  - Split layout per card (image left, content right), swipeable carousel with prev/next arrows (no dots), keyboard navigation, and edge fade hints
  - Isolated stylesheet (`assets/css/cards.css`) to avoid conflicts with the calculator
  - Section Header “pill” with editable text and border colors (64px radius)
  - Price suffix displayed beside price (default “/month”) and optional price tagline below
  - Admin image preview for each card; Duplicate action to clone a card in-place
  - Top-right badge: 50px height with 14px/24px padding, radius 0 20px 0 20px, configurable gradient (default #FD9651 → #F0532C)
  - Arrows use PNG icons with hover variants; disabled at edges; scrollbar hidden
  - Card visuals: image fills media area (object-fit: cover); card border-radius 20px; text column left padding
  - Typography controls for card content: Name, Tagline, Price, Price Suffix, Price Tagline, Benefits (family, weight, size, color, line-height)

## Installation
1. Copy `irank-calc-cards` to `wp-content/plugins/`.
2. Activate in WP Admin → Plugins.
3. Go to Settings → IRANK Calc & Cards to adjust defaults.

## Usage
### Blocks
- Weight Loss Calculator: add images (Before/After), set min/max/step, initial weight, factor, unit, colors, CTA text.
- Product Cards: in the Inspector
  - Section: set Section Header, Heading, Subheading
  - Cards: add 3+ cards. Each card has Name, Tagline, Price, Price Suffix (default “/month”), Price Tagline (below price), Benefits (one per line), Badge, CTA Text/URL, Image. Use Duplicate to clone a card and image preview to verify selection.
  - Colors: section gradient (start/end), card background, CTA colors + hover, badge text color, badge gradient start/end
  - Typography:
    - Header/Heading/Subheading font family, weight, size, line-height, color; Section Header border color
    - Card content defaults:
      - Name: Poppins, 700, 36px, line-height 40px, #3B3B3A
      - Tagline: Poppins, 600, 16px, line-height 22px, #3B3B3A
      - Price: Poppins, 700, 56px, line-height 56px, #3B3B3A
      - Price Suffix: Poppins, 400, 16px, line-height 22px, #3B3B3A
      - Price Tagline: Poppins, 400, 14px, line-height 16px, #3B3B3A
      - Benefits: Poppins, 600, 16px, line-height 22px, #3B3B3A

### Lead form overlay
- The calculator CTA opens a modal overlay with a simple lead form (Full name, Email, Phone). Email is validated client‑side, then the lead is saved to the database via admin‑ajax. A large centered “Thanks!” message appears and the popup auto‑closes after a few seconds.

### Viewing leads
- WP Admin → Tools → IRANK Leads
  - Columns: Date, Name, Email, Phone, Page, Weight, Loss
  - Export CSV available

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
- Leads (Full name, Email, Phone) plus context are stored in your WordPress database (`wp_irank_calc_leads`).
- Saved fields: timestamp, page ID, weight, loss, session ID, referrer, user agent, anonymized IP hash.
- No third‑party tracking or REST endpoints are used by the plugin.

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
