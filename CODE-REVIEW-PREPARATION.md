# Code Review Preparation

## Context
The plugin ships two server‑rendered Gutenberg blocks with a lightweight, dependency‑free frontend: the Weight Loss Calculator and the Product Cards carousel. The Calculator provides a slider‑driven UX, a before/after reveal, and a lead form modal that persists to WP via admin‑ajax. Product Cards implement a scroll‑snap carousel with arrows and a mobile viewer bar. 

## Technical decisions
- **SSR dynamic blocks (PHP)**
  - Faster first paint, SEO‑friendly, simpler hosting; no build chain required.
- **No framework; tiny vanilla JS**
  - `frontend.cards.js`: scroll‑snap carousel (arrows, keyboard, viewer bar), `aria-current` for active state.
  - `frontend.calculator.js`: range slider updates, animated loss, before/after reveal, modal open/close, email validation, ajax submit.
- **CSS variables + scoped stylesheets**
  - Cards: `--cards-cta-*`, `--badge-*`, `--peek`, `--gap`, `--media-h-mobile` in `assets/css/cards.css`.
  - Calculator: gradient start/end and typography via `assets/css/frontend.css` with inline overrides.
**Settings and assets**
  - Options page (Settings → IRANK Calc & Cards) stores global defaults (ranges, factor, unit, gradients, optional Nohemi URL).
  - Fonts: Poppins from Google; optional Nohemi via user‑provided CSS URL.
  - Asset registration uses `filemtime()` for cache busting and depends ordering (frontend, cards, editor).
**Lead storage and admin UI**
  - Leads saved via `admin-ajax.php` into `wp_irank_calc_leads` created by `dbDelta()` on activation.
  - Tools → IRANK Leads lists recent rows with CSV export (nonce + cap check).
- **Mobile viewer bar (dots) only on mobile**
  - Markup container rendered in PHP; JS creates buttons; CSS shows on mobile, hides on desktop. Active is `#F0532C`.
- **Image container**
  - Mobile height via `--media-h-mobile` (default 275px); `object-fit: cover; object-position: center` to avoid gaps.
- **Precise peek on mobile**
  - `grid-auto-columns: calc(100% - var(--peek) - var(--gap))` for predictable peeking.
- **Accessibility**
  - Cards: buttons with labels; `aria-current` for active dot; `aria-disabled` on arrows; keyboard support.
  - Calculator: `aria-live` for dynamic loss; Escape closes modal; keyboard support for reveal handle and modal.
- **Asset versioning**
  - `filemtime()` for cache busting.
- **Admin cleanup**
  - Removed unused IRANK Reports admin page and events table creation.

## Alternatives considered
- **Swiper/Glide vs CSS scroll‑snap + tiny JS**
  - Rejected: heavier payload, bundle/tooling, reduced SSR simplicity.
- **REST endpoints vs admin‑ajax** (calculator leads)
  - REST would add auth and headers concerns; admin‑ajax remains simplest for server‑side persistence.
- **Use a form plugin vs custom lead capture**
  - Form plugins add weight and coupling; custom modal + ajax gives full control and no extra deps.
- **Modal overlay vs separate landing page for lead capture**
  - Overlay keeps context and increases conversion; separate page adds navigation friction.
- **Client‑only CTA label change vs `wp_is_mobile()`**
  - JS/CSS alternative would avoid cache variance but adds FOUC risk; server check kept for simplicity. If full‑page cache serves one HTML to all devices, consider CSS/JS fallback.
- **`object-fit: contain` vs `cover`**
  - Contain avoided letterboxing; spec required full‑bleed card visuals → chose cover.
- **Always‑visible dots vs mobile‑only**
  - Dots on desktop clutter the design; kept arrows + edge fades on desktop; dots on mobile only.
- **Scrollbar as progress indicator**
  - Inconsistent across browsers; replaced by explicit viewer bar with accessible buttons.
- **No‑build vs modern block build (block.json, React)**
  - Chose no‑build for portability to PHP‑only hosting; build pipeline adds complexity without clear UX gain for this scope.

## Scaling and future‑proofing
- **Editor controls for mobile tuning (Cards)**
  - Expose `--peek`, `--gap`, `--media-h-mobile` as block attributes with sensible defaults.
- **Per‑card theming**
  - Allow per‑card CSS variables (e.g., CTA/badge/dot color) via data attributes or inline style vars.
- **Image performance**
  - Add `srcset`/`sizes` on card images when media IDs are available; lazy‑load below fold.
- **Internationalization**
  - Wrap user‑facing strings with `__()`/`_x()`; load text domain.
- **Accessibility hardening**
  - Focus ring styling; ensure contrast ratios; announce slide changes with polite `aria-live` if needed.
- **Testing**
  - Add Playwright/Cypress for E2E on mobile viewports; PHPUnit smoke tests for render callbacks.
- **Caching considerations**
  - If a page cache collapses device variants, switch mobile CTA label override to CSS/JS (or use Vary by UA if available).
- **DB migrations**
  - Leads table already created via `dbDelta()`. Future schema changes should include upgrade routines keyed by plugin version.
- **Editor UX**
  - Consider a Block Pattern for typical Product Cards configuration.
- **Leads pipeline**
  - Optional webhooks (action hooks) or REST endpoints for CRM integration; pagination/search in admin; spam mitigation (honeypot/reCAPTCHA), rate limiting.
- **Packaging and CI**
  - Add PHPCS (WordPress Coding Standards), linting, and release scripts; wp.org readme and assets if publishing.

## Risks and mitigations
- **UA detection via `wp_is_mobile()`**
  - Risk: cache or proxy may serve desktop HTML to mobile; Mitigation: prefer CSS/JS label in cached environments.
- **Custom CSS overrides in themes**
  - Risk: collisions; Mitigation: styles are in a scoped file with specific class names. Keep selectors narrowly scoped.
- **Long images with extreme aspect ratios**
  - Risk: over‑crop with `cover`; Mitigation: allow per‑card override (`contain`) if needed via a class.
- **Spam and PII handling (Calculator leads)**
  - Risk: spam submissions/PII exposure; Mitigation: server‑side sanitization, capability checks for exports, nonces, optional CAPTCHA/honeypot, data retention policy.
- **Performance regressions**
  - Risk: unthrottled scroll/resize handlers; Mitigation: rAF throttling already used; keep payloads small.

## Review focus areas
- **Security**: attribute escaping/sanitization, capability checks, nonces on exports, SQL safety.
- **Accessibility**: calculator modal semantics + Escape handling; viewer bar `aria-current`; keyboard navigation.
- **Performance**: CSS/JS payload size, rAF‑throttled scroll/resize, image loading strategy.
- **Maintainability**: clear separation of concerns (PHP renderers, CSS variables, small JS), file structure, naming.

## Manual test checklist (condensed)
- **Product Cards**
  - Carousel swipe/scroll; arrows disable at edges; keyboard left/right.
  - Viewer bar visible on mobile; active dot follows; clicking a dot scrolls to the slide.
  - CTA mobile label/layout correct; desktop unchanged.
  - Image fills container (cover + center); mobile height variable respected.
- **Calculator**
  - Slider drag/keyboard updates current weight; loss animates with correct factor.
  - Before/After reveal works; labels clickable; respects reduced motion.
  - CTA opens modal; email validation gating; submit saves via admin‑ajax; success screen shows and auto‑closes.
  - Lead appears in Tools → IRANK Leads; CSV export works.
- **General**
  - Settings page saves and applies defaults; optional Nohemi URL loads fonts.
  - Assets enqueued once; cache busting via filemtime works.
  - Documentation (README/HOWTO/TECHNICAL) matches implemented behavior; CHANGELOG current.

## Decision log summary
- SSR, no framework; CSS variables; viewer bar on mobile; admin‑ajax for leads; settings + asset versioning; removed legacy Reports.
