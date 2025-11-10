# WooCommerce Product Import

This guide covers configuring and running the WooCommerce import pipeline that syncs catalog data from a remote WordPress/WooCommerce store into the application.

## 1. Prerequisites

1. Generate a WooCommerce REST API key pair (consumer key and secret) with **Read** permissions.
2. Note the base REST endpoint for your store. For a standard install this is `https://yourstore.com/wp-json/wc/v3`.
3. Ensure outbound HTTPS requests from the application server can reach the WooCommerce site.

## 2. Environment Configuration

Add the following variables to your `.env` file:

```dotenv
WOOCOMMERCE_BASE_URL="https://yourstore.com/wp-json/wc/v3"
WOOCOMMERCE_CONSUMER_KEY="ck_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
WOOCOMMERCE_CONSUMER_SECRET="cs_xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"

# Optional tuning
WOOCOMMERCE_PER_PAGE=50        # Max 100
WOOCOMMERCE_TIMEOUT=30         # Seconds
WOOCOMMERCE_VERIFY_SSL=true    # Set false only if the store uses a self-signed certificate
WOOCOMMERCE_CURRENCY=USD       # Default currency code applied to imported variants
```

After updating `.env`, clear the configuration cache if applicable:

```bash
php artisan config:clear
```

## 3. Running the Import

The Artisan command `catalog:import-woocommerce` performs the import. It is idempotent and can be run repeatedly; products, categories, variants, options, and media are created or updated as needed, and missing variants/media are soft-deleted.

### Basic usage

```bash
php artisan catalog:import-woocommerce
```

### Helpful options

| Option | Description |
|--------|-------------|
| `--per-page=` | Override the WooCommerce page size (1-100). Defaults to `WOOCOMMERCE_PER_PAGE`. |
| `--since=` | Import only products modified after the provided ISO-8601 timestamp. |
| `--product=` | Limit the import to specific WooCommerce product IDs (repeatable). |
| `--skip-categories` | Skip category synchronisation (useful when categories are managed locally). |
| `--dry-run` | Execute inside a transaction and roll back, allowing you to preview the summary without persisting changes. |

Example: re-import two specific products without touching categories.

```bash
php artisan catalog:import-woocommerce \
    --product=555 \
    --product=777 \
    --skip-categories
```

### What gets synchronised

- **Categories**: hierarchy, visibility, and metadata; stored with the WooCommerce ID in `categories.data`.
- **Products**: core details, specifications (non-variation attributes), status, publication timestamp, and remote metadata.
- **Options & Values**: generated for variation attributes (e.g., colour/size) and connected to variants.
- **Variants**: pricing, inventory, shipping flags, default selection, remote identifiers, and option values.
- **Media**: product and variant imagery (remote URLs stored in `product_media.path`).

## 4. Testing

Automated coverage exists in `tests/Feature/ImportWooCommerceProductsTest.php` which fakes WooCommerce responses and validates the end-to-end command behaviour. Run the full suite with:

```bash
php artisan test --testsuite=Feature --filter=ImportWooCommerceProductsTest
```

If PHP is not available on the environment where you deploy this code, run the tests within your local development environment or CI pipeline.

## 5. Notes & Best Practices

- Use `--dry-run` before the first live import to confirm connectivity and review the summary.
- Products or variants removed remotely are soft-deleted locally; review the summary table for `deleted` counts.
- The importer assumes the WooCommerce store uses a single currency; configure `WOOCOMMERCE_CURRENCY` to match.
- For large catalogs, consider scheduling the command (e.g., via cron) and using the `--since` flag for incremental updates.
