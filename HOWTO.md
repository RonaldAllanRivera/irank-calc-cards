# How to test

- **Activate plugin**
  - WP Admin → Plugins → “IRANK Calc & Cards” → Activate.
- **Settings**
  - WP Admin → Settings → “IRANK Calc & Cards”.
  - Adjust min/max/step, loss factor, unit, gradient colors, tracking toggle.
- **Add blocks**
  - Edit any page.
  - Insert block “Weight Loss Calculator”.
  - Set before/after images, texts, etc. in the right sidebar (Inspector).
  - Insert block “Product Cards” and add 3+ cards via the Inspector repeater.
- **Frontend behavior**
  - Drag slider → weight and “loss” animate.
  - Before/After image reveal moves smoothly.
  - Click CTA → opens same‑page Results overlay showing weight/loss.
  - Tracking event is sent with `sendBeacon`.
- **Reports**
  - WP Admin → Tools → IRANK Reports → view totals by date, export CSV.

# Short Gutenberg guide (no‑build)

- **Open Inspector Controls**
  - Click the block → right sidebar shows settings.
- **Calculator block controls**
  - Min/Max/Initial/Step
  - Loss factor (e.g. 0.15)
  - Unit (lbs/kg)
  - Before/After image pickers
  - CTA text
  - Timer toggle + text
  - Gradient start/end
- **Product cards block controls**
  - In “Cards” panel, click “Add Card”.
  - For each card: Name, Tagline, Price, Benefits (one per line), Badge, CTAs.
  - Use ↑/↓ to reorder, Remove to delete.
- **Save/Preview**
  - Update the page, then View to test interactions.