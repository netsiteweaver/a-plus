## 001 ? Mega Electronics E-commerce Analysis

### Project Context
- Target stack: Laravel 12 API backend, Vue 3 + Vite SPA frontend, Tailwind CSS design system.
- Hosting: Ubuntu VPS with Apache (PHP-FPM), MySQL datastore, Redis for cache/queues (planned).
- Catalog size: < 1000 products with variant support, curated related-item suggestions.
- Public registration required; robust RBAC to partition customer and staff/admin access.

### Functional Highlights
- **Catalog & Variants:** Products with rich attributes; variant-level pricing/stock, filters and spec tables, quick-view and comparison features.
- **Recommendations:** Manual related-product curation with automated similarity fallback (category + attributes) and caching.
- **Customer Journey:** Tailored home/landing blocks, mega menu navigation, faceted search, variant-aware PDP, cart, checkout with payment/shipping integrations, account dashboard.
- **Content & Marketing:** Dynamic banners, scheduled promotions, blog/news, reviews, loyalty roadmap, analytics instrumentation.

### Architecture & Modules
- **Backend (Laravel 12):**
  - Domains: Catalog, Inventory, Pricing, Promotions, Customers, Orders, Payments, Shipping, Reviews, Content.
  - Auth & RBAC: `spatie/laravel-permission`, multi-guard (customer vs admin), 2FA, rate limiting.
  - Services: Jobs/queues, cache strategy, search indexing (MySQL baseline ? Scout/Meilisearch optional).
- **Frontend (Vue 3):**
  - SPA with SSR/hydration for SEO pages, Pinia store, Vue Router, Vue Query/Axios for data.
  - Tailwind theming aligned to Woodmart palette; component library mapped from mirrored static snapshot.
  - Performance: code-splitting, lazy loading, responsive imagery directives.

### Data & Integrations
- **Database schema drivers:**
  - Tables for `products`, `product_variants`, `product_attributes`, `attribute_values`, `categories`, `category_product`, `inventories`, `prices`, `promotions`, `related_products`, `orders`, `order_items`, `customers`, `addresses`, `payments`, `shipments`.
  - Auditing for admin activity, soft deletes for catalog safety.
- **External services (planned):** payment gateways (Stripe/Adyen/PayPal), shipping aggregators (Shippo/EasyPost), analytics (GA4, Segment), error monitoring (Sentry), CDN for assets, optional search engine.

### DevOps & Tooling
- GitHub Actions pipeline: linting (ESLint, PHPStan, Pint), tests (Pest + Vitest), build artifacts.
- Environment setup via Docker Compose for local parity; deployment with zero-downtime strategy (Envoyer/Deployer or Actions + rolling releases).
- Server prep: PHP 8.3 + FPM, Node LTS, Supervisor for queues, scheduled tasks via cron, TLS via Let?s Encrypt.

### Workflows (Summary)
- **Admin catalog management:** Create/edit product ? attach media ? define attributes ? create variants ? curate related products ? publish ? reindex caches.
- **Customer flow:** Discover via home/search/category ? filter ? PDP (select variant) ? add to cart ? checkout ? order confirmation.
- **Related items pipeline:** On product publish run job ? use manual picks or algorithmic fallback ? cache result ? expose via API widget.

### Actionable Next Steps
1. Finalize attribute taxonomy, related-item rules, payment & shipping providers, localization scope.
2. Produce detailed schema diagrams and API contracts for catalog, cart/checkout, and recommendations.
3. Bootstrap repository (Laravel + Vue monorepo), configure tooling, and begin building Tailwind component system referencing the static snapshot.
