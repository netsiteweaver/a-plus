# Quick Fix: Server Upload Issues

## Problem
✅ Local: Upload works fine  
❌ Server: Preview works but upload fails  

## Quick Solution

### On Your Server, Run:

```bash
# Navigate to project
cd /path/to/a-plus

# Run setup script
./setup-storage-server.sh

# Or manually:
cd app
php artisan storage:check
```

## Most Common Fixes

### 1. Storage Permissions
```bash
cd app
chmod -R 775 storage
chmod -R 775 bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 2. Storage Symlink
```bash
cd app
php artisan storage:link
```

### 3. PHP Upload Limits
Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```
Then restart: `sudo systemctl restart php8.2-fpm`

### 4. Nginx Upload Limit
Add to server block:
```nginx
client_max_body_size 10M;
```
Then restart: `sudo systemctl restart nginx`

## Check What's Wrong

```bash
cd app

# Run diagnostics
php artisan storage:check

# Check logs
tail -f storage/logs/laravel.log

# Test permissions
touch storage/app/public/brands/logos/test.txt
```

## Updated Features

The BrandController now:
- ✅ Automatically creates missing directories
- ✅ Checks write permissions before upload
- ✅ Logs detailed error messages
- ✅ Returns helpful error responses

## Check Logs

When upload fails, check the Laravel log:
```bash
tail -100 storage/logs/laravel.log
```

Look for:
- "Failed to create storage directory"
- "Storage directory is not writable"
- "Failed to store file"

## Full Documentation

See `docs/008-server-upload-troubleshooting.md` for complete guide.

## Test Upload

1. SSH into server
2. Run: `php artisan storage:check`
3. Fix any issues reported
4. Try upload again from browser
5. Check logs if still failing

## Need Help?

Run this and save the output:
```bash
cd app
php artisan storage:check
ls -la storage/app/public
ls -la public/storage
php -i | grep -E 'upload_max_filesize|post_max_size'
tail -50 storage/logs/laravel.log
```

