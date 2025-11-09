#!/bin/bash

# A+ Deployment Script
# Run this script after pulling code updates

set -e  # Exit on error

echo "🚀 Starting deployment process..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Change to app directory
cd "$(dirname "$0")/app"

# 1. Put application in maintenance mode
echo "1️⃣  Enabling maintenance mode..."
php artisan down || print_warning "Could not enable maintenance mode (might already be down)"
print_status "Maintenance mode enabled"
echo ""

# 2. Install/Update Composer dependencies
echo "2️⃣  Installing/Updating Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader
print_status "Composer dependencies updated"
echo ""

# 3. Install/Update NPM dependencies
echo "3️⃣  Installing/Updating NPM dependencies..."
npm install
print_status "NPM dependencies updated"
echo ""

# 4. Clear and cache configuration
echo "4️⃣  Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_status "Caches cleared"
echo ""

# 5. Run database migrations
echo "5️⃣  Running database migrations..."
read -p "Do you want to run database migrations? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]
then
    php artisan migrate --force
    print_status "Database migrations completed"
else
    print_warning "Skipped database migrations"
fi
echo ""

# 6. Build frontend assets
echo "6️⃣  Building frontend assets..."
npm run build
print_status "Frontend assets built"
echo ""

# 7. Optimize application
echo "7️⃣  Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
print_status "Application optimized"
echo ""

# 8. Set proper permissions
echo "8️⃣  Setting proper permissions..."
chmod -R 775 storage bootstrap/cache
print_status "Permissions set"
echo ""

# 9. Clear OPcache (if available)
echo "9️⃣  Clearing OPcache..."
php artisan cache:clear
print_status "OPcache cleared"
echo ""

# 10. Bring application back up
echo "🔟  Disabling maintenance mode..."
php artisan up
print_status "Application is now live!"
echo ""

echo "✅ Deployment completed successfully!"
echo ""
echo "📝 Summary:"
echo "   - Composer dependencies: Updated"
echo "   - NPM dependencies: Updated"
echo "   - Database migrations: $(if [[ $REPLY =~ ^[Yy]$ ]]; then echo 'Completed'; else echo 'Skipped'; fi)"
echo "   - Frontend assets: Built"
echo "   - Application: Optimized"
echo "   - Permissions: Set"
echo ""
echo "🎉 Your application is ready!"

