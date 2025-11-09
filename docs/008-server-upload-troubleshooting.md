# Server Upload Troubleshooting Guide

## Issue: Image Preview Works but Upload Fails on Server

When the image preview works (client-side) but the actual upload fails, it's typically a server configuration issue.

## Quick Fix - Run Setup Script

On your server, run:

```bash
cd /path/to/your/project
./setup-storage-server.sh
```

This will automatically:
- Create necessary directories
- Set proper permissions
- Create storage symlink
- Clear caches
- Run diagnostic checks

## Manual Diagnostic Steps

### Step 1: Run Storage Check

```bash
cd app
php artisan storage:check
```

This command will check:
- ✓ Storage directory exists and is writable
- ✓ Brands logos directory exists
- ✓ Storage symlink is properly configured
- ✓ PHP upload limits
- ✓ File write permissions

### Step 2: Check Common Issues

#### A. Storage Directory Permissions

**Check current permissions:**
```bash
ls -la storage/app/public
```

**Fix permissions:**
```bash
# Option 1: Using chmod (if you own the files)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Option 2: Using chown (requires sudo)
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

**Note:** Replace `www-data` with your web server user:
- **Nginx/Ubuntu:** `www-data`
- **Apache/CentOS:** `apache`
- **Apache/Ubuntu:** `www-data`

#### B. Storage Symlink

**Check if symlink exists:**
```bash
ls -la public/storage
```

**Recreate symlink:**
```bash
cd app
rm -rf public/storage  # Remove old symlink if exists
php artisan storage:link
```

**Verify symlink:**
```bash
# Should show: public/storage -> ../storage/app/public
ls -la public/storage
```

#### C. PHP Upload Limits

**Check current limits:**
```bash
php -i | grep -E 'upload_max_filesize|post_max_size|max_file_uploads'
```

**Required minimum values:**
- `upload_max_filesize`: 2M or higher
- `post_max_size`: 2M or higher (should be >= upload_max_filesize)
- `max_file_uploads`: 20 or higher

**Fix PHP limits:**

1. Find your `php.ini` file:
```bash
php --ini
```

2. Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

3. Restart PHP-FPM:
```bash
# Ubuntu/Debian
sudo systemctl restart php8.2-fpm

# CentOS/RHEL
sudo systemctl restart php-fpm
```

#### D. Web Server Limits

**For Nginx:**

Edit your server block (usually in `/etc/nginx/sites-available/your-site`):

```nginx
server {
    # ... other configuration ...
    
    client_max_body_size 10M;
    client_body_timeout 60s;
    
    # ... rest of configuration ...
}
```

Restart Nginx:
```bash
sudo nginx -t  # Test configuration
sudo systemctl restart nginx
```

**For Apache:**

Option 1: Edit `.htaccess` in your project root:
```apache
php_value upload_max_filesize 10M
php_value post_max_size 10M
LimitRequestBody 10485760
```

Option 2: Edit Apache config:
```apache
<Directory /path/to/your/app/public>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    LimitRequestBody 10485760
</Directory>
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

#### E. SELinux Issues (CentOS/RHEL)

If using SELinux, you may need to set proper contexts:

```bash
# Check if SELinux is enabled
getenforce

# Set proper SELinux context for storage
sudo chcon -R -t httpd_sys_rw_content_t storage/
sudo chcon -R -t httpd_sys_rw_content_t bootstrap/cache/

# Or disable SELinux temporarily to test (not recommended for production)
sudo setenforce 0
```

## Debugging with Logs

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for error messages when attempting upload. Common errors:

**"Failed to create storage directory"**
- Solution: Fix directory permissions

**"Storage directory is not writable"**
- Solution: Run `chmod -R 775 storage`

**"Failed to store file"**
- Solution: Check disk space, permissions, and PHP limits

### Check Web Server Logs

**Nginx:**
```bash
sudo tail -f /var/log/nginx/error.log
```

**Apache:**
```bash
sudo tail -f /var/log/apache2/error.log
```

### Enable Debug Mode (Temporarily)

In `.env` file:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**Warning:** Disable debug mode in production after troubleshooting!

## Verification Steps

After fixing issues, verify the setup:

### 1. Run Diagnostic Check
```bash
php artisan storage:check
```

### 2. Test File Write
```bash
cd storage/app/public/brands/logos
touch test.txt
echo "test" > test.txt
```

If this fails, permissions are incorrect.

### 3. Test Upload via Browser
1. Log into admin panel
2. Edit a brand
3. Upload a small test image
4. Check browser Network tab (F12 → Network)
5. Look for the upload request and response

### 4. Verify File Was Created
```bash
ls -la storage/app/public/brands/logos/
```

You should see the uploaded file with a timestamp_uniqid.ext filename.

## Common Error Messages & Solutions

### "Session store not set on request"
See previous fix - session middleware configuration.

### "413 Request Entity Too Large"
- **Cause:** Nginx client_max_body_size too small
- **Fix:** Increase `client_max_body_size` in Nginx config

### "500 Internal Server Error" on upload
- **Check:** Laravel logs (`storage/logs/laravel.log`)
- **Common causes:** 
  - Storage permissions
  - Disk space full
  - PHP memory limit

### "The logo failed to upload"
- **Check:** PHP upload limits
- **Check:** File permissions
- **Check:** Available disk space: `df -h`

### Symlink shows as broken
- **Cause:** Absolute path vs relative path
- **Fix:** Remove and recreate symlink with `php artisan storage:link`

## Production Deployment Checklist

When deploying to production, ensure:

- [ ] Storage directories exist
- [ ] Permissions are set (775 for directories, 664 for files)
- [ ] Ownership is correct (web server user)
- [ ] Storage symlink is created
- [ ] PHP upload limits are sufficient (min 2M, recommend 10M)
- [ ] Web server upload limits are configured
- [ ] `.env` has correct `APP_URL`
- [ ] Caches are cleared
- [ ] SELinux contexts set (if applicable)
- [ ] Tested upload with actual file

## Environment-Specific Notes

### Shared Hosting
Some shared hosts restrict:
- File permissions (max 755)
- PHP settings (unchangeable)
- Symlink creation

**Solutions:**
- Contact hosting support
- Use alternative upload method (external storage)
- Upgrade to VPS/dedicated server

### Docker
If using Docker, ensure:
- Storage volume is mounted correctly
- Container user has write permissions
- Volume permissions are correct on host

Example `docker-compose.yml`:
```yaml
services:
  app:
    volumes:
      - ./storage:/var/www/html/storage:rw
    user: "${UID}:${GID}"
```

### Laravel Forge/Vapor
These platforms handle storage automatically, but verify:
- Deployment script runs `php artisan storage:link`
- Environment variables are set correctly

## Still Having Issues?

1. **Check disk space:**
   ```bash
   df -h
   ```

2. **Check inode usage:**
   ```bash
   df -i
   ```

3. **Test with minimal file:**
   - Create 1KB test image
   - Try uploading

4. **Verify network:**
   - Check browser console for CORS errors
   - Verify API endpoint is accessible

5. **Review application logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

6. **Test from command line:**
   ```bash
   curl -X POST http://your-domain.com/api/admin/brands/1/logo \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -F "logo=@/path/to/test-image.jpg"
   ```

## Support Resources

- Check Laravel logs: `storage/logs/laravel.log`
- Check web server logs
- Review error responses in browser Network tab
- Use `php artisan storage:check` for diagnostics

## Contact Information

If issues persist after following this guide:
1. Run `php artisan storage:check` and save output
2. Check `storage/logs/laravel.log` for errors
3. Check browser Network tab for API response
4. Provide this information when seeking help

