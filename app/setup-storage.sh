#!/bin/bash

# Laravel Storage Directory Setup Script
# This script creates the required storage directory structure with proper permissions

set -e  # Exit on error

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Detect the user who should own the files
if [ -n "$SUDO_USER" ]; then
    OWNER="$SUDO_USER"
else
    OWNER="$(stat -c '%U' .)"
fi

echo "Setting up Laravel storage directories..."
echo "Working directory: $(pwd)"
echo "Setting owner to: $OWNER"
echo ""

# Create storage directory structure
echo "Creating directory structure..."
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/testing
mkdir -p storage/logs
mkdir -p storage/app/public

# Set proper permissions
echo "Setting permissions..."

# Fix ownership if running as root/sudo
if [ "$(id -u)" -eq 0 ]; then
    echo "Running with elevated privileges, fixing ownership..."
    chown -R "$OWNER:$OWNER" storage 2>/dev/null || true
    chown -R "$OWNER:$OWNER" bootstrap/cache 2>/dev/null || true
    chown "$OWNER:$OWNER" .env 2>/dev/null || true
fi

# Set directory permissions
chmod -R 775 storage 2>/dev/null || true
chmod -R 775 bootstrap/cache 2>/dev/null || true

# Set file permissions for .env
chmod 664 .env 2>/dev/null || true

# Create .gitignore files
echo "Creating .gitignore files..."

# storage/app/.gitignore
cat > storage/app/.gitignore << 'EOF'
*
!public/
!.gitignore
EOF

# storage/app/public/.gitignore
cat > storage/app/public/.gitignore << 'EOF'
*
!.gitignore
EOF

# storage/framework/.gitignore
cat > storage/framework/.gitignore << 'EOF'
compiled.php
config.php
down
events.scanned.php
maintenance.php
routes.php
routes.scanned.php
schedule-*
services.json
EOF

# storage/framework/cache/.gitignore
cat > storage/framework/cache/.gitignore << 'EOF'
*
!data/
!.gitignore
EOF

# storage/framework/cache/data/.gitignore
cat > storage/framework/cache/data/.gitignore << 'EOF'
*
!.gitignore
EOF

# storage/framework/sessions/.gitignore
cat > storage/framework/sessions/.gitignore << 'EOF'
*
!.gitignore
EOF

# storage/framework/testing/.gitignore
cat > storage/framework/testing/.gitignore << 'EOF'
*
!.gitignore
EOF

# storage/framework/views/.gitignore
cat > storage/framework/views/.gitignore << 'EOF'
*
!.gitignore
EOF

# storage/logs/.gitignore
cat > storage/logs/.gitignore << 'EOF'
*
!.gitignore
EOF

echo ""
echo "✓ Storage directories created successfully!"
echo "✓ Permissions set to 775"
echo "✓ File ownership fixed"
echo "✓ .gitignore files added"
echo ""
echo "Storage structure:"
find storage -type d 2>/dev/null | sort | sed 's|[^/]*/| |g;s| \+||'
echo ""
echo "Cache path is now available at: storage/framework/cache/data"
echo ""
echo "Note: If you encounter permission errors, run this script with sudo:"
echo "  sudo ./setup-storage.sh"

