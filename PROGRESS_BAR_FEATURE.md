# WooCommerce Import Progress Bar

## Overview

The WooCommerce import command now displays a real-time progress bar showing:
- Number of products imported
- Current action (downloading images, processing)
- Visual progress bar with percentage
- Product names being processed

## What You'll See

### During Import

```
🚀 Starting WooCommerce import...

Synchronising WooCommerce categories…

    15 products | 📥 Downloading 4 images for: MacBook Pro 16"
 ████████░░░░░░░░░░░░░░░░░░░░  28%
```

### Progress Indicators

- **Product Count**: Shows how many products have been processed
- **Image Download**: Shows when images are being downloaded (if enabled)
- **Product Name**: Displays the name of the current product being processed
- **Progress Bar**: Visual indicator of overall progress
- **Percentage**: Numeric progress indicator

### On Completion

```
    151 products | ✅ Completed
 ████████████████████████████ 100%

✅ Import completed successfully.
+------------+---------+---------+-----------------+
| Entity     | Created | Updated | Deleted/Skipped |
+------------+---------+---------+-----------------+
| Categories | 17      | 0       | 0               |
| Products   | 151     | 1       | 0               |
| Variants   | 151     | 1       | 0               |
| Media      | 132     | 98      | 1               |
+------------+---------+---------+-----------------+
```

## Features

### Smart Status Updates

The progress bar shows different messages based on what's happening:

1. **Initializing**: Starting the import
2. **Synchronising categories**: Fetching and syncing categories
3. **Downloading images**: When product images are being downloaded
   - Shows how many images
   - Shows product name (truncated to 40 characters)
4. **Product completed**: Advances the progress bar after each product

### Works With All Import Options

The progress bar works with all import options:

```bash
# Standard import with progress
php artisan catalog:import-woocommerce

# Dry-run with progress
php artisan catalog:import-woocommerce --dry-run

# Specific products with progress
php artisan catalog:import-woocommerce --product=123,456,789

# With pagination settings
php artisan catalog:import-woocommerce --per-page=50

# Skip categories (faster start)
php artisan catalog:import-woocommerce --skip-categories
```

### Verbose Mode

For debugging, add `-v` to see detailed logs alongside the progress bar:

```bash
php artisan catalog:import-woocommerce -v
```

This shows:
- Detailed HTTP requests
- Image download URLs
- Error messages
- Database queries (with `-vv`)

## When Image Download is Disabled

If you set `WOOCOMMERCE_DOWNLOAD_IMAGES=false`, the progress bar still works but doesn't show the download messages:

```
    15 products | Processing products...
 ████████░░░░░░░░░░░░░░░░░░░░  28%
```

## Implementation Details

### How It Works

1. **Progress Callback**: The importer emits events during processing
2. **Event Handling**: The command listens for events and updates the progress bar
3. **Non-Blocking**: Progress updates don't slow down the import
4. **Clean Output**: Progress bar cleanly finishes on completion or error

### Events Emitted

- `downloading_images`: When product images are being downloaded
  - Data: `product_id`, `product_name`, `image_count`
  
- `product_completed`: When a product finishes importing
  - Data: `product_id`, `product_name`

### Error Handling

If the import fails:
1. Progress bar finishes cleanly
2. Error message is displayed
3. Statistics are still shown (if available)

## Performance Impact

The progress bar has **minimal performance impact**:
- Event callbacks are lightweight
- Updates happen only after each product (not per image)
- No additional database queries
- No file I/O overhead

## Tips

1. **Large Imports**: For catalogs with 1000+ products, the progress bar helps monitor progress over long-running imports

2. **Remote Servers**: When running on a remote server via SSH, the progress bar still works and updates in real-time

3. **Background Jobs**: If you run the import in a queue/background job, consider using the `--quiet` flag to disable the progress bar

4. **Automation**: In automated scripts, you can check the exit code:
   ```bash
   php artisan catalog:import-woocommerce
   if [ $? -eq 0 ]; then
       echo "Import successful"
   else
       echo "Import failed"
   fi
   ```

5. **Parallel Imports**: Avoid running multiple imports simultaneously - the progress bars may conflict

## Related Configuration

The progress bar behavior respects:
- `WOOCOMMERCE_DOWNLOAD_IMAGES`: Shows download status if true
- Output verbosity flags (`-v`, `-vv`, `-vvv`)
- `--quiet` flag: Disables all output including progress bar
- `--dry-run` flag: Progress bar works normally but no data is saved

## Comparison

### Before (No Progress Bar)
```
Starting import...
[long wait with no feedback]
Import completed.
```

### After (With Progress Bar)
```
🚀 Starting WooCommerce import...

Synchronising WooCommerce categories…

    42 products | 📥 Downloading 6 images for: Wireless Mouse
 ██████████████░░░░░░░░░░░░░░  52%
```

Much better user experience! ✨

