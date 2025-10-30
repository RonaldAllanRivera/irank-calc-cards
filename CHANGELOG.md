# Changelog

## 0.1.8 - Product Cards: full typography controls
- Added typography controls for card content with defaults:
  - Name (Poppins, 700, 36px, 40px, #3B3B3A)
  - Tagline (Poppins, 600, 16px, 22px, #3B3B3A)
  - Price (Poppins, 700, 56px, 56px, #3B3B3A)
  - Price Suffix (Poppins, 400, 16px, 22px, #3B3B3A)
  - Price Tagline (Poppins, 400, 14px, 16px, #3B3B3A)
  - Benefits (Poppins, 600, 16px, 22px, #3B3B3A)
- Editor: added matching controls in Typography panel
- Frontend: applied styles inline with sanitization and existing font resolver
- Back-compat: preserved legacy benefitColor as fallback

## 0.1.7 - Product Cards badge gradient, arrow icons, image fit, layout cleanup
- Badge moved to the top-right; 50px height with 14px/24px padding, radius 0 20px 0 20px
- New Colors: Badge Gradient Start/End (defaults: `#FD9651` → `#F0532C`); removed legacy Badge BG option
- Badge gradient applied via CSS variables; fixed content overlap by adding top padding when badged
- Carousel arrows now use PNG icons with hover variants; native scrollbar hidden; dots UI removed
- Card visuals: image set to object-fit: cover with no media padding; content column gets left padding; card border-radius set to 20px
- Docs: README updated to reflect new badge gradient controls, arrow behavior, and visual tweaks

## 0.1.6 - Product Cards section header, split layout, styling controls
- Added Section Header + Heading + Subheading to Product Cards (with Typography panel)
- Per-card image field; split layout (image left, content right)
- Colors panel: section gradient, card background, CTA bg/text + hover bg/text/border, badge colors
- Isolated stylesheet `assets/css/cards.css` to avoid conflicts with calculator
- Default font set to Poppins (no Nohemi required)
- Docs: README updated with Product Cards details; HOWTO includes editor tutorial
 - Section Header styles: 64px pill with editable text and border colors; spacing tuned to match design
 - Removed Section Background solid color in favor of gradient start/end only; cleaned attributes and CSS
 - Enqueue `irank-cc-cards` in Product Cards render so styles reliably apply on frontend
 - Price UX: per‑card Price Suffix (defaults to "/month") rendered beside price; optional Price Tagline under price
 - Editor UX: image preview shown in each card; Duplicate action to clone a card inline

## 0.1.5 - Remove REST tracking, lead form UX, settings cleanup
- Removed all custom REST API routes and frontend REST calls
- Lead form overlay validates only email and saves leads via admin-ajax to `wp_irank_calc_leads`
- Removed "Enable Tracking" setting (and related option handling)
- Cleaned frontend JS and markup (no `data-rest-root`, `trackEvent` is a no-op)
- Styled lead form inputs to match existing UI and added success screen with auto-close
- Updated README/HOWTO to reflect behavior and privacy

## 0.1.4 - Button styles, CTA width, BA label UX
- Buttons panel in block editor for configurable colors:
  - CTA background/text + hover background/text/border
  - Before/After label background/text + hover background/text/border
- CTA button width set to 70% of panel and centered; 64px radius
- Before/After labels set to 64px radius
- Before/After labels are clickable to reveal left/right with animation
- Labels auto-hide near edges (≤8% hides Before, ≥92% hides After)

## 0.1.3 - Typography controls and fonts
- Added Typography panel with per-text controls: font family, weight, size, color
- Limited font choices to Poppins and Nohemi (per Figma)
- Loaded Poppins (500/600/700) via Google Fonts for editor and frontend
- Applied defaults per spec:
  - Headline: Nohemi, 600, 48px, white
  - Current weight: Poppins, 600, 14px, white
  - Weight loss: Poppins, 600, 14px, white
  - Before/After: Poppins, 600, 12px, white
  - CTA: Poppins, 600, 18px, white
  - Timer: Poppins, 500, 14px, white
- README and HOWTO updated

## 0.1.2 - Editable text labels and gradient updates
- Added editable text fields in block editor for all calculator text:
  - Headline ("How much weight can you lose")
  - Current weight label
  - Weight loss label
  - Before/After labels
  - CTA text
  - Timer text
- Moved CTA and Timer text to "Text Labels" panel in block editor
- Updated default gradient colors to `#FFBB8E` (start) and `#f67a51` (end)
- Changed gradient angle to 100° for a more balanced look
- Updated documentation in README.md and HOWTO.md

## 0.1.1 - Blocks working, overlay + images, reports, docs
- Weight Loss Calculator dynamic block
  - Overlay now hidden on load; opens on CTA; closes on outside click and Escape
  - Animated loss value; range slider updates; session persistence
  - Before/After visual with responsive images (`wp_get_attachment_image`, `srcset`)
  - Added labels “Before/After”; keyboard support for reveal handle
  - Removed proof bar (sliding text)
  - Visual min-height 490px; BA images capped to 490px height; responsive width
- Product Cards dynamic block
  - Editor repeater in Inspector; CSS scroll‑snap carousel with prev/next + dots
- First‑party tracking
  - DB table `wp_irank_calc_events`; REST `POST /irank/v1/track` with `sendBeacon`
  - Admin Tools → IRANK Reports page with totals and CSV export
- Settings page (ranges, factor, unit, colors, tracking toggle)
- Asset versioning via `filemtime`
- Documentation: README.md, PLAN.md, HOWTO.md

## 0.1.0 - Initial scaffolding
- Plugin bootstrap and structure
- Enqueue/editor/frontend asset registration
- Block registration stubs
