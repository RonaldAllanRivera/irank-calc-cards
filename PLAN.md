# Project Plan: IRANK Calc & Cards (No-Build WP Plugin)

## Phase 0: Foundation
- Objectives
  - No-build Gutenberg blocks (ES5) compatible with WP 6.8.3 / PHP 8.4
  - Zero external dependencies
  - Same-page Results overlay (vanilla JS)
  - First-party conversion tracking (DB + REST + sendBeacon)
- Deliverables
  - Plugin scaffold, settings page, enqueues
  - Calculator block (dynamic)
  - Product cards block (dynamic)
  - Results overlay UI
  - Basic admin report (counts + CSV)

## Phase 1: Scaffold
- Bootstrap: main plugin file, autoload includes
- Settings: options (ranges, factor, unit, colors, tracking toggle)
- Activation: create results defaults and events table
- Enqueue: editor and frontend assets only when blocks present

## Phase 2: Calculator Block (priority)
- Attributes: min/max/step/initial, lossFactor, unit, images, CTA text, timer option, colors
- Editor: inspector controls + live preview
- Frontend: slider updates, animated loss, before/after reveal, proof bar, CTA opens results overlay
- Tracking: sendBeacon on CTA with weight/loss

## Phase 3: Product Cards Block
- Editor: repeater for 3+ cards (name, tagline, price, benefits, badge, image, CTA). Section heading + subheading.
- Styling controls: section gradient start/end, card background, CTA (bg/text + hover bg/text/border), badge bg/text, basic typography sizes.
- Frontend: CSS scroll-snap carousel with prev/next + dots. Split layout per card (image left, content right). Poppins font.
- Implementation: SSR render with sanitized attributes; CSS variables for theming; dedicated cards.css to avoid conflicts with calculator.

## Phase 4: Results Overlay
- UI layer rendered with calculator block
- Reads current state; shows weight/loss summary and a CTA
- Persists last values in sessionStorage for resilience

## Phase 5: Tracking & Admin
- DB schema: wp_irank_calc_events (created_at, page_id, weight, loss, session_id, referrer, ua, ip_hash)
- Admin page: KPIs + table + CSV export (lightweight canvas chart optional)

## Phase 6: Polish & Docs
- A11y: labels, aria-live, keyboard slider
- Performance: only-needed assets, lazy images, reduced motion
- Docs: README, Gutenberg quick guide, configuration notes

## Out of Scope (for v1)
- Third-party analytics (GA/GTM)
- Advanced theming system beyond CSS variables

## Milestones
- M1: Scaffold + Calculator working (results overlay + tracking call)
- M2: Product cards working
- M3: Admin report + docs
