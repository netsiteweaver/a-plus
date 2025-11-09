#!/bin/bash

# A+ Quick Update Script
# For development - skips maintenance mode and runs faster

set -e  # Exit on error

echo "⚡ Quick update starting..."
echo ""

# Colors for output
GREEN='\033[0;32m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

# Change to app directory
cd "$(dirname "$0")/app"

# Update Composer dependencies
echo "📦 Updating Composer..."
composer install --no-interaction
print_status "Done"

# Update NPM dependencies
echo "📦 Updating NPM..."
npm install
print_status "Done"

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_status "Done"

# Build assets
echo "🏗️  Building assets..."
npm run build
print_status "Done"

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
print_status "Done"

echo ""
echo "✅ Quick update completed!"

