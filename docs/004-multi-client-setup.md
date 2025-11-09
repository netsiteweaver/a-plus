# Multi-Client Deployment Guide

## Overview

This application is designed for multi-instance deployment where the same codebase can serve multiple clients with complete data isolation. Each client deployment has its own database with fully customizable settings, navigation, content, and pages—all manageable through the admin panel without code changes.

## Architecture

### System Configuration (.env)
- Database credentials
- Application key
- Mail drivers
- Cache configuration
- Third-party API keys

### Client Configuration (Database)
- Company information (legal name, website name, contact details)
- Branding (logo, colors, favicon)
- Navigation menus (primary, utility, footer)
- Content blocks (hero sections, featured categories, deals)
- CMS pages (About, Services, Support, etc.)
- Feature toggles (reviews, wishlist, comparison, etc.)

## Deployment Steps for New Client

### 1. Environment Setup

Create a new `.env` file for the client deployment:

```bash
# Copy from example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Configuration

Update `.env` with client-specific database:

```env
APP_NAME="Client Company Name"
APP_URL=https://client-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=client_database_name
DB_USERNAME=database_user
DB_PASSWORD=database_password
```

### 3. Run Migrations and Seeders

```bash
# Run migrations
php artisan migrate

# Seed configuration data (optional - provides defaults)
php artisan db:seed --class=SettingsSeeder
php artisan db:seed --class=NavigationSeeder
php artisan db:seed --class=ContentBlockSeeder
php artisan db:seed --class=PageSeeder

# Or seed everything including catalog
php artisan db:seed
```

### 4. Configure Settings via Admin Panel

Login to admin panel at `/admin` and configure:

#### Company Settings (`/admin/settings`)
- Website name (displayed in header, browser title)
- Tagline
- Description
- Logo URL (path to uploaded logo)
- Favicon URL

#### Legal Settings
- Legal company name (used in documents, invoices)
- Registration number
- Tax ID
- Legal registered address

#### Business Settings
- Currency (USD, EUR, BDT, etc.)
- Currency symbol
- Timezone
- Date/time formats
- Primary email and phone
- Support email and phone
- Tax settings

#### Contact Settings
- Primary business address
- Business hours (by day of week)

#### Social Media
- Facebook, Twitter, Instagram, LinkedIn, YouTube URLs

#### Branding
- Primary color (hex code)
- Secondary color (hex code)
- Show/hide tagline
- Show/hide promo banner
- Promo banner text

#### Feature Toggles
- Enable/disable reviews
- Enable/disable wishlist
- Enable/disable product comparison
- Enable/disable loyalty program
- Enable/disable gift cards
- Enable/disable quick view
- Enable/disable newsletter

### 5. Customize Navigation

Navigate to `/admin/navigation` to manage menus:

**Primary Menu:**
- Create/edit main navigation items
- Configure mega menus with columns and sub-items
- Add hero content for mega menus
- Reorder items by drag-and-drop

**Utility Menu:**
- Top bar utility links (Support, Track Order, etc.)

**Footer Menu:**
- Footer navigation links

### 6. Manage Content Blocks

Navigate to `/admin/content-blocks`:

**Available Block Types:**
- **Hero**: Main homepage hero section
- **Featured Categories**: Showcase product categories
- **Featured Products**: Highlight specific products
- **Daily Deals**: Promotional deals section
- **Promo Banner**: Promotional announcements
- **Text Block**: Rich text content
- **Image Block**: Image with caption

**Per Block Configuration:**
- Title and content (JSON structure based on type)
- Page assignment (home, about, services, etc.)
- Position/order
- Status (draft, published, archived)

### 7. Manage CMS Pages

Navigate to `/admin/pages`:

- Create/edit pages (About, Services, Support, etc.)
- Rich text content editor
- SEO meta fields (title, description)
- Template selection (default, full-width, custom)
- System pages cannot be deleted

### 8. Upload Branding Assets

1. Upload logo and favicon to `/public/images/client-name/`
2. Update settings with correct paths:
   - Logo URL: `/images/client-name/logo.png`
   - Favicon URL: `/images/client-name/favicon.ico`

## API Endpoints

### Public Configuration API

**Get Public Settings:**
```http
GET /api/config/settings
```
Returns all public settings grouped by category.

**Get Navigation:**
```http
GET /api/config/navigation/{location}
```
Locations: `primary`, `utility`, `footer`

**Get Content Blocks:**
```http
GET /api/config/content/{page}
```
Returns all published blocks for the specified page.

### Admin Configuration API

All admin endpoints require authentication and appropriate permissions.

**Settings Management:**
- `GET /api/admin/settings` - List all settings
- `GET /api/admin/settings/group/{group}` - Get settings by group
- `PUT /api/admin/settings/bulk` - Bulk update settings
- `POST /api/admin/settings` - Create setting
- `PUT /api/admin/settings/{id}` - Update setting
- `DELETE /api/admin/settings/{id}` - Delete setting

**Navigation Management:**
- `GET /api/admin/navigation` - List all menus
- `GET /api/admin/navigation/{location}` - Get specific menu
- `POST /api/admin/navigation/items` - Create navigation item
- `PUT /api/admin/navigation/items/{id}` - Update item
- `DELETE /api/admin/navigation/items/{id}` - Delete item
- `POST /api/admin/navigation/items/reorder` - Reorder items

**Content Blocks:**
- `GET /api/admin/content-blocks` - List blocks (filterable by page/type)
- `POST /api/admin/content-blocks` - Create block
- `PUT /api/admin/content-blocks/{id}` - Update block
- `DELETE /api/admin/content-blocks/{id}` - Delete block
- `POST /api/admin/content-blocks/reorder` - Reorder blocks

**Pages:**
- `GET /api/admin/pages` - List pages
- `POST /api/admin/pages` - Create page
- `PUT /api/admin/pages/{id}` - Update page
- `DELETE /api/admin/pages/{id}` - Delete page (except system pages)

## Settings Reference

### Company Group
- `website_name` - Display name in header/title
- `tagline` - Company tagline/slogan
- `description` - Company description
- `logo_url` - Path to logo file
- `favicon_url` - Path to favicon

### Legal Group
- `legal_name` - Official registered name
- `registration_number` - Company registration number
- `tax_id` - Tax identification number
- `legal_address` - Registered address (JSON)

### Business Group
- `currency` - Currency code (USD, EUR, etc.)
- `currency_symbol` - Currency symbol ($, €, etc.)
- `timezone` - Default timezone
- `date_format` - PHP date format
- `time_format` - PHP time format
- `phone_number` - Primary phone
- `email` - Primary email
- `support_email` - Support email
- `support_phone` - Support phone
- `tax_included` - Whether prices include tax (boolean)

### Contact Group
- `primary_address` - Main business address (JSON)
- `business_hours` - Operating hours by day (JSON)

### Social Group
- `facebook` - Facebook page URL
- `twitter` - Twitter/X profile URL
- `instagram` - Instagram profile URL
- `linkedin` - LinkedIn company page URL
- `youtube` - YouTube channel URL

### Features Group
- `enable_reviews` - Product reviews (boolean)
- `enable_wishlist` - Wishlist feature (boolean)
- `enable_comparison` - Product comparison (boolean)
- `enable_loyalty` - Loyalty program (boolean)
- `enable_gift_cards` - Gift cards (boolean)
- `enable_quick_view` - Quick view modal (boolean)
- `enable_newsletter` - Newsletter subscription (boolean)

### Branding Group
- `primary_color` - Main brand color (hex)
- `secondary_color` - Secondary color (hex)
- `show_tagline` - Display tagline (boolean)
- `show_promo_banner` - Show promo banner (boolean)
- `promo_banner_text` - Banner text content

## Cache Management

The configuration system uses caching for performance:

**Clear All Caches:**
```bash
php artisan cache:clear
```

**Programmatic Cache Clearing:**
```php
use App\Models\Setting;
use App\Models\NavigationMenu;
use App\Models\ContentBlock;

Setting::clearCache();
NavigationMenu::clearCache();
ContentBlock::clearCache();
```

Caches are automatically cleared when updating settings, navigation, or content through the admin API.

## Backup and Restore

### Backup
```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup uploaded files
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/images/
```

### Restore
```bash
# Restore database
mysql -u username -p database_name < backup_20250109.sql

# Restore files
tar -xzf uploads_backup_20250109.tar.gz
```

## Troubleshooting

### Settings Not Updating
1. Clear cache: `php artisan cache:clear`
2. Check database connection
3. Verify setting key exists in database

### Navigation Not Showing
1. Check menu location matches frontend request
2. Verify navigation items are marked as `is_active`
3. Clear navigation cache

### Content Blocks Not Displaying
1. Verify blocks are published (`status = 'published'`)
2. Check page parameter matches
3. Clear content cache

## Best Practices

1. **Separate Databases**: Each client should have its own isolated database
2. **Regular Backups**: Schedule daily database backups
3. **Asset Organization**: Store client assets in separate folders (`/public/images/client-name/`)
4. **Environment Variables**: Keep system-critical config in `.env`, everything else in database
5. **Version Control**: Keep the codebase in version control, but NOT `.env` or uploads
6. **Testing**: Test configuration changes in staging before applying to production

## Support

For issues or questions:
- Technical Support: support@aplustech.com
- Documentation: [Link to full docs]
- GitHub Issues: [Repository URL]

