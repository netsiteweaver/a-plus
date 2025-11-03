## 003 ? Catalog Data Model

### Goals
- Support <1000 SKUs with multi-variant products, rich attribute specs, and curated related items.
- Allow flexible filtering (attributes per category) and future growth (inventory, pricing, promotions).
- Keep schema Laravel-friendly with clear foreign keys, indexes, and soft-deletes where edits are common.

### Core Tables
- **brands** ? optional manufacturer data (name, slug, website).
- **categories** ? hierarchical tree (nested set columns optional later); store slug, status, SEO fields.
- **category_product** ? pivot linking products to multiple categories (supports featured/position).
- **products** ? base entity (SKU family) with content, status, brand, authoring metadata, JSON specs, SEO.
- **product_media** ? ordered images/videos/documents; variant-specific flag column.
- **product_options** ? per-product option definitions (e.g., Color, Capacity).
- **product_option_values** ? list of values per option (e.g., Midnight Black, 256 GB) including swatch data.
- **product_variants** ? purchasable units referencing product, SKU, barcode, price, stock, dimensions.
- **product_variant_option_value** ? pivot mapping variant ? option values (many-to-many for n-dimensional options).
- **attributes** ? global attribute definitions (data type, filterable flag, unit).
- **attribute_values** ? optional enumerations for finite attributes (color family, chipset).
- **product_attribute_values** ? bridge storing product ? attribute values; supports freeform and enumerated values.
- **related_products** ? curated cross-sell/upsell pairs with relation_type (related, accessory, replacement, bundle).

### Inventory & Pricing Hooks
- Variants carry MSRP and compare-at prices, cost, currency, inventory SKU, stock tracking fields.
- Leave room to integrate inventory adjustments and price book tables later (e.g., `variant_prices`, `inventories`).

### Metadata & Soft Deletes
- Products, variants, options, option values, and media use `softDeletes()` for safe editorial changes.
- Timestamps everywhere to enable activity tracking & cache invalidation.

### Index Strategy
- Slugs (brands, categories, products) unique with indexes.
- Searchable columns indexed: `products.status`, `products.brand_id`, `product_variants.sku`.
- Compound unique keys for pivots (e.g., `product_variant_option_value` unique on `(variant_id, option_value_id)`).

### Laravel Considerations
- Migrations include foreign key constraints with cascades on delete where appropriate (soft deletes mitigate data loss).
- Model scaffolding roadmap:
  - `Product`, `Brand`, `Category`, `ProductVariant`, etc. with relationships (belongsToMany, hasMany).
  - Observers to sync `default_variant_id` and variant pricing summary fields.
- Seeders can bootstrap demo catalog, attributes, and sample related product relationships.

### Next Steps
1. Generate Eloquent models + factories for each catalog table.
2. Implement repositories/services for product import, attribute assignment, related-product suggestion jobs.
3. Expose API endpoints (catalog browse, PDP, related items, admin CRUD) leveraging the new schema.
