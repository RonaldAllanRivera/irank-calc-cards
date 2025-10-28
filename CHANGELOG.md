# Changelog

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
