# Weight Loss Calculator — Technical Documentation

## Overview
This plugin provides two dynamic Gutenberg blocks. The Weight Loss Calculator block implements a slider-driven experience with animated loss estimate, a before/after visual reveal, and a lead form modal that saves leads to WordPress via admin‑ajax (no REST).

- Primary file: `irank-calc-cards.php`
- Frontend assets: `assets/js/frontend.calculator.js`, `assets/css/frontend.css`
- Admin settings: `includes/class-settings.php`
- Documentation: `README.md`, `HOWTO.md`, `CHANGELOG.md`

## Architecture and Data Flow
- Server‑side rendering (SSR): Blocks are rendered in PHP (no build tools).
- Frontend enhancement: Small vanilla JS augments UI (slider updates, before/after, modal, email validation, AJAX submit).
- Lead storage: `admin-ajax.php` handles `action=irank_cc_lead` and inserts into `$wpdb->prefix.'irank_calc_leads'`.
- Admin views: Tools → IRANK Leads table with CSV export.

Data flow steps
1) User adjusts slider → weight value updates and loss is computed (weight × factor).
2) User clicks CTA → modal opens with lead form.
3) Email validated client‑side → `fetch(admin-ajax.php)` posts lead + context.
4) PHP sanitizes and inserts into DB → success → form switches to a centered Thank‑you message → modal auto-closes.

## Feature coverage vs. original spec
- Current Weight Slider (100–400 lbs, draggable, live value): ✅
- Weight Loss Potential Display (×0.15, smooth animation, "-XX lbs" formatting): ✅
- Before/After Visual with draggable reveal and smooth transitions: ✅
- Call‑to‑Action with hover state + timer text; captures weight: ✅ (weight and loss saved with the lead)
- Scrolling proof bar: ➖ Omitted as non‑essential for MVP (can be added later if needed)

## Why this implementation
- SSR Gutenberg blocks: fast first paint, SEO friendly, and simple hosting (no Node build).
- Vanilla JS only: smaller payloads, fewer moving parts, easier maintenance.
- admin‑ajax for leads: avoids REST auth and server config pitfalls while remaining native to WordPress.
- Minimal dependencies: consistent with shared/PHP‑only hosting and local stacks like Laragon.

## How to edit or extend
- Block PHP renderers
  - Calculator: `irank_cc_render_calculator_block()` in `irank-calc-cards.php`
  - Cards: `irank_cc_render_cards_block()`
- Frontend logic
  - Calculator UI and modal/submit: `assets/js/frontend.calculator.js`
  - Styles: `assets/css/frontend.css`
- Settings screen
  - Fields and sanitization: `includes/class-settings.php`

Common extensions
- Change the loss formula
  - Update the factor default in `irank_cc_default_options()` and the frontend calculator math in `frontend.calculator.js`.
- Add new form fields
  - Frontend: add fields in the modal markup and collect them into `FormData` in `frontend.calculator.js`.
  - Server: accept/sanitize fields in `irank_cc_ajax_lead()` and add DB columns (via an activation or upgrade routine), then surface in the admin leads table.
- Add server‑side validation
  - Enforce required fields in `irank_cc_ajax_lead()` before insert; return `wp_send_json_error()` on failure.

## Editable content approach
- Editor-side attributes (Gutenberg) control:
  - Text labels (headline, labels, CTA, timer)
  - Ranges (min/max/step), factor, unit, colors, images
  - Typography (families, weights, sizes, colors) per text element
- Site‑wide defaults via Settings → IRANK Calc & Cards
  - Min/Max/Step, Loss factor, Unit, Gradient colors, optional Nohemi CSS URL
- Frontend uses inline CSS variables for CTA/labels + themeable stylesheet to respect editor choices.

## Accessibility and UX
- `aria-live` for dynamic loss value.
- Keyboard support for revealing image handle and closing modal (Escape).
- Reduced motion respected.
- Success state: hides form controls, shows large centered Thank‑you message; modal auto‑closes.

## Security, privacy, and data model
- Sanitization: `sanitize_text_field`, `sanitize_email`, `floatval`, `intval`, `wp_unslash`.
- Email validation: client‑side regex + server‑side `is_email()` check.
- Stored context:
  - `created_at`, `page_id`, `full_name`, `email`, `phone`, `weight`, `loss`, `session_id`, `referrer`, `user_agent`, `ip_hash`
- Privacy: no third‑party tracking or REST endpoints out of the box; data stays in your WP DB.

## Trade‑offs considered
- REST vs admin‑ajax
  - Chose admin‑ajax to eliminate auth/headers/proxy issues; downside: REST‑style versioning and tooling are not leveraged.
- Client‑only vs server persistence
  - Chose server persistence for real lead capture; adds DB writes and requires schema setup.
- No build tooling vs modern build
  - Chose no‑build for simplicity and portability; downside: no bundling/tree‑shaking.

## Conversion tracking options
- Built‑in conversion: lead insert = conversion.
  - Leads visible at Tools → IRANK Leads and exportable as CSV.
- Recommended extension for analytics (not included):
  - In `irank_cc_ajax_lead()`, fire a custom action after successful insert:
    ```php
    do_action( 'irank_cc_lead_saved', $wpdb->insert_id, array(
      'page_id' => $page_id,
      'weight'  => $weight,
      'loss'    => $loss,
      'email'   => $email,
    ) );
    ```
  - A theme/plugin can hook this to send events to GA/Meta/etc.

## Testing checklist
- Slider: drag on desktop and touch; number animates smoothly; min/max respected.
- Before/After: handle draggable; labels clickable; hiding near edges works on small screens.
- Modal: opens on CTA; Escape/outside click closes; email validation gating works.
- Submit: valid email saves to DB and shows centered success; auto‑closes after ~5s.
- Admin: Tools → IRANK Leads shows new row; CSV export downloads properly.

## Performance notes
- Lightweight JS and CSS; no framework runtime.
- SSR reduces layout shift and improves perceived speed.
- Asset versioning with `filemtime()` for cache busting.

## Versioning and upgrades
- See `CHANGELOG.md` for feature history.
- DB tables created in `irank_cc_activate()`; if adding columns later, include an upgrade routine using `dbDelta()`.

## Known gaps and future ideas
- Scrolling proof bar (from original brief) intentionally omitted; could be added as a small, accessible marquee with pause‑on‑hover and reduced‑motion support.
- Optional webhooks or REST endpoints (behind nonce/cap checks) if external CRM integration is required.
- Settings for custom success message copy and modal auto‑close duration.
