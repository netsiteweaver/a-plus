#!/bin/bash

# Storage Setup Script for Production Server
# This script fixes common storage and upload issues

echo "🔧 Setting up storage for brand logo uploads..."
echo ""

# Navigate to app directory
cd "$(dirname "$0")/app" || exit 1

# Color codes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Step 1: Create storage directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public/brands/logos
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
print_success "Storage directories created"

# Step 2: Set permissions
echo ""
echo "🔐 Setting permissions..."

# Check if running as root or with sudo
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root"
    # Set ownership to www-data (common web server user)
    chown -R www-data:www-data storage
    chown -R www-data:www-data bootstrap/cache
    print_success "Ownership set to www-data:www-data"
else
    print_warning "Not running as root - you may need to set ownership manually"
    echo "  Run: sudo chown -R www-data:www-data storage bootstrap/cache"
fi

# Set permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
print_success "Permissions set to 775"

# Step 3: Create storage symlink
echo ""
echo "🔗 Creating storage symlink..."
if [ -L "public/storage" ]; then
    print_warning "Symlink already exists, removing old one"
    rm public/storage
elif [ -d "public/storage" ]; then
    print_warning "Directory exists at public/storage, removing"
    rm -rf public/storage
fi

php artisan storage:link
if [ $? -eq 0 ]; then
    print_success "Storage symlink created"
else
    print_error "Failed to create symlink"
    exit 1
fi

# Step 4: Clear caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
print_success "Caches cleared"

# Step 5: Run diagnostic check
echo ""
echo "🔍 Running diagnostic check..."
php artisan storage:check

# Step 6: Check web server configuration
echo ""
echo "📋 Web Server Notes:"
echo ""
echo "If uploads still fail, check your web server configuration:"
echo ""
echo "For Nginx, add to your server block:"
echo "  client_max_body_size 2M;"
echo ""
echo "For Apache, add to .htaccess or httpd.conf:"
echo "  php_value upload_max_filesize 2M"
echo "  php_value post_max_size 2M"
echo ""
echo "For PHP-FPM, check php.ini:"
echo "  upload_max_filesize = 2M"
echo "  post_max_size = 2M"
echo "  max_file_uploads = 20"
echo ""

print_success "Storage setup complete!"
echo ""
echo "If you still have issues, check the Laravel logs at:"
echo "  storage/logs/laravel.log"

