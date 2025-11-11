# WooCommerce Product Image Download Feature

## Overview

The WooCommerce importer now supports downloading product images from the remote WooCommerce server and storing them locally on your server. This is controlled by the `download_images` configuration option.

## Configuration

Edit `config/woocommerce.php`:

```php
'download_images' => filter_var(env('WOOCOMMERCE_DOWNLOAD_IMAGES', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
    ?? true,
```

Or set it in your `.env` file:

```env
# Download and store images locally (default: true)
WOOCOMMERCE_DOWNLOAD_IMAGES=true

# Or just use remote URLs without downloading (faster imports)
WOOCOMMERCE_DOWNLOAD_IMAGES=false
```

## How It Works

### When `download_images = true` (Default)

1. **During Import**: Each product image is downloaded from the WooCommerce server
2. **Storage Location**: Images are saved to `storage/app/public/products/{product_id}/{filename}`
3. **Database**: Images are stored with:
   - `disk`: `public`
   - `path`: `products/{product_id}/{filename}`
4. **Fallback**: If download fails, it falls back to storing the remote URL

### When `download_images = false`

1. **No Download**: Images URLs are stored as-is
2. **Database**: Images are stored with:
   - `disk`: `remote`
   - `path`: `https://woocommerce-store.com/wp-content/uploads/...`
3. **Faster Imports**: Significantly faster as no image downloads occur

## Storage Structure

```
storage/app/public/products/
├── 1/                          # Product ID 1
│   ├── laptop-macbook-0.jpg    # Product image (index 0)
│   ├── laptop-macbook-1.jpg    # Product image (index 1)
│   └── abc123def456-variant-5.jpg  # Variant image
├── 2/                          # Product ID 2
│   ├── mouse-wireless-0.png
│   └── mouse-wireless-1.png
...
```

## Image Naming Convention

Images are named using one of these patterns:

1. **Original filename** (preferred): `{sanitized-original-name}-{identifier}.{ext}`
   - Example: `laptop-macbook-pro-0.jpg`

2. **Hash-based** (fallback): `{md5-hash}-{identifier}.{ext}`
   - Example: `a1b2c3d4e5f6-0.jpg`

Where:
- `{identifier}` is the image index (0, 1, 2...) or `variant-{id}` for variant-specific images
- `{ext}` is automatically detected from the URL or Content-Type header

## Supported Image Formats

- **JPEG** (.jpg, .jpeg)
- **PNG** (.png)
- **GIF** (.gif)
- **WebP** (.webp)
- **SVG** (.svg)

## Usage Examples

### Import with image downloading (default)

```bash
php artisan catalog:import-woocommerce
```

### Import without downloading images (faster)

```bash
# Set in .env first
WOOCOMMERCE_DOWNLOAD_IMAGES=false

# Then run import
php artisan catalog:import-woocommerce
```

### Dry-run to test (won't persist anything)

```bash
php artisan catalog:import-woocommerce --dry-run
```

## Error Handling

The importer handles download failures gracefully:

1. **Connection Issues**: Logs warning, falls back to remote URL
2. **Invalid Images**: Skips the image, logs error
3. **Storage Failures**: Falls back to remote URL
4. **Timeouts**: 30-second timeout per image

All failures are logged to `storage/logs/laravel.log`.

## Performance Considerations

### With Image Download (download_images = true)
- **Slower**: Takes longer due to HTTP requests and file I/O
- **Storage**: Requires disk space for images
- **Bandwidth**: Uses server bandwidth to download
- **Reliability**: Images stored locally, not dependent on remote server
- **Speed**: Faster page loads (images served from your server)

### Without Image Download (download_images = false)
- **Faster**: Import completes much quicker
- **No Storage**: Minimal disk usage
- **No Bandwidth**: No download bandwidth used
- **Dependency**: Relies on remote WooCommerce server being available
- **Speed**: Slower page loads (images loaded from remote server)

## Accessing Downloaded Images

Images stored locally are accessible via:

```
https://your-domain.com/storage/products/{product_id}/{filename}
```

Make sure you've run:
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

## Metadata Preservation

All images store the original WooCommerce URL in the `data` JSON field:

```json
{
  "source": "woocommerce",
  "woocommerce_image_id": 123,
  "woocommerce_url": "https://store.com/wp-content/uploads/image.jpg"
}
```

This allows you to:
- Re-download images later if needed
- Track the original source
- Implement custom fallback logic

## Tips

1. **First Import**: Use `download_images=false` for faster initial import, then re-import with `download_images=true` to download images for specific products

2. **Large Catalogs**: Consider importing in batches:
   ```bash
   php artisan catalog:import-woocommerce --per-page=50
   ```

3. **Monitoring**: Watch the logs during import:
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Storage Space**: Monitor disk usage before importing large catalogs:
   ```bash
   df -h
   ```

5. **Re-imports**: Existing images won't be re-downloaded unless the media record is updated

