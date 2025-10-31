# Weight Loss Calculator - How to use (for editors)

- Add the calculator to a page
  - Edit a page in WordPress → click "+" → search "Weight Loss Calculator" → insert.
- Configure the calculator
  - In the right sidebar (Block settings), set: min/max/step, initial weight, loss factor (0.15 = 15%), unit (lbs/kg), gradient colors, CTA text, timer toggle/text.
  - Upload your Before and After images. Drag the divider on the preview to check the visual slider.
- Publish and view
  - Click "Update/Publish" → View the page.
  - The CTA opens a pop‑up with a form (Full name, Email, Phone). Enter a valid email and submit.
  - You’ll see a big “Thanks! We’ll be in touch soon.”, and the pop‑up will close automatically.
- See collected leads
  - WP Admin → Tools → IRANK Leads. You can review entries or Export CSV.

# Product Cards — How to use in the editor

- Add “Product Cards” block to a page.
- In the sidebar:
  - Section: set Section Header, Heading, Subheading.
  - Typography: Section Header text color and border color; Heading/Subheading font family, weight, size, color.
  - Cards: add 3+ cards. For each: Name, Tagline, Price, Price Suffix (default “/month”), Price Tagline (below price), Benefits (one per line), Badge (optional), CTA Text/URL, Image.
  - Colors: set Section Gradient Start/End, card background, CTA colors and hover, badge colors.
- Frontend
  - Price shows with suffix beside it (e.g., “/month”); price tagline appears below.
  - Swipe cards on mobile (scroll-snap). Use prev/next buttons on all sizes; the viewer bar (dots) appears on mobile.
  - Mobile specifics:
    - CTA: “Select Medication”, centered 85% width, padding 20px/12px, one line.
    - Image area: sized via mobile variable, fills with cover crop anchored to the bottom (center bottom).
    - Section header pill: one line at 14px.

# How to test

- **Activate plugin**
  - WP Admin → Plugins → “IRANK Calc & Cards” → Activate.
- **Settings**
  - WP Admin → Settings → “IRANK Calc & Cards”.
  - Adjust min/max/step, loss factor, unit, gradient colors.
- **Add blocks**
  - Edit any page.
  - Insert block “Weight Loss Calculator”.
  - Set before/after images, texts, etc. in the right sidebar (Inspector).
  - Insert block “Product Cards” and add 3+ cards via the Inspector repeater.
- **Frontend behavior**
  - Drag slider → weight and “loss” animate.
  - Before/After image reveal moves smoothly.
  - Click CTA → opens lead form overlay (Full Name, Email, Phone).
  - Only Email is required and validated client‑side.
  - Submit → form hides and a large centered “Thanks! We’ll be in touch soon.” message appears.
  - Overlay auto‑closes after ~5 seconds.
  - Submission is saved to the database via admin‑ajax.
  - On mobile, the popup width is limited to ~90% of the viewport.
  - Lead form inputs/textarea render at ~80% of the form width, centered.
- **Leads (admin)**
  - WP Admin → Tools → IRANK Leads → verify submissions (Date, Name, Email, Phone, Page, Weight, Loss).
  - Use “Export CSV” to download the data.

# Short Gutenberg guide (no‑build)

## Editing calculator text
- Select the Weight Loss Calculator block.
- In the sidebar, open Text Labels.
- Editable fields:
  - Headline ("How much weight can you lose")
  - Current weight label
  - Weight loss label
  - Before label / After label
  - CTA text
  - Timer text
- The Show Timer toggle is under Calculator Settings.
- Existing pages keep saved copy. To use new defaults, clear a field or re‑insert the block.

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
  - For each card: Name, Tagline, Price, Price Suffix, Price Tagline, Benefits (one per line), Badge, CTA Text/URL, Image.
  - Use ↑/↓ to reorder, Remove to delete.
- **Save/Preview**
  - Update the page, then View to test interactions.

## Load Nohemi font (optional)
- Get the cloud CSS link for your licensed Nohemi kit (e.g., Adobe Fonts/Typekit or your CDN).
- Go to WP Admin → Settings → IRANK Calc & Cards.
- Paste the URL into “Nohemi CSS URL (optional)”.
- Save Changes, then hard‑refresh the editor and the frontend.