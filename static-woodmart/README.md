## Woodmart Mega Electronics Snapshot

- **Source:** `https://woodmart.xtemos.com/mega-electronics/` captured via `wget` on 2025-11-03.
- **Entry point:** Open `woodmart.xtemos.com/mega-electronics/index.html` in a browser or serve the folder over HTTP.
- **Structure:**
  - `woodmart.xtemos.com/mega-electronics/` ? main landing page plus ancillary pages (feeds, rss).
  - `woodmart.xtemos.com/wp-content/` ? theme assets, images, scripts, styles.
  - `woodmart.xtemos.com/robots.txt` ? upstream robots definition (mirrored only for completeness).

- **How to preview locally:**
  1. From `static-woodmart/`, run `python -m http.server 8080` (or any static server).
  2. Visit `http://localhost:8080/woodmart.xtemos.com/mega-electronics/`.

- **Caveats:**
  - External resources (Google Fonts, analytics, third-party scripts) still load from their CDNs.
  - Dynamic WooCommerce features (cart, filters, search) require WordPress back-end; the snapshot captures only the rendered HTML/JS/CSS available without interaction.
  - Some admin endpoints (e.g., `xmlrpc.php`) were blocked (HTTP 403) during mirroring; they are not present locally.

- **Next steps:**
  - Use this snapshot as a design reference to recreate components in Laravel/Vue.
  - Inventory components and map them to the future Tailwind/Vue design system.
