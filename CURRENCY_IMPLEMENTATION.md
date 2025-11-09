# Currency Implementation Guide

## Overview

Currency settings have been successfully integrated throughout the entire site. The currency is now stored in the database settings and used site-wide for all price displays.

## What Was Implemented

### 1. Database Settings (✅ Already Existed)

The currency settings are stored in the `settings` table with the following keys:
- `currency` - The ISO 4217 currency code (e.g., USD, EUR, GBP)
- `currency_symbol` - The display symbol (e.g., $, €, £)

These settings are part of the `business` group and are marked as `is_public = true` so they're available to the frontend.

### 2. Backend Configuration

**Location:** `app/database/seeders/SettingsSeeder.php`

Default values:
```php
['key' => 'currency', 'value' => ['MUR'], 'group' => 'business', 'type' => 'string', 'is_public' => true],
['key' => 'currency_symbol', 'value' => ['Rs'], 'group' => 'business', 'type' => 'string', 'is_public' => true],
```

The settings are exposed via the public API at `/api/config/settings`.

### 3. Frontend Implementation

#### 3.1 Currency Composable

**File:** `app/resources/js/composables/useCurrency.js`

A new composable provides currency formatting utilities:

```javascript
import { useCurrency } from '@/composables/useCurrency';

const { formatCurrency, formatDiscount, formatPriceRange } = useCurrency();

// Format a price
formatCurrency(999.99); // Returns "Rs1,000" (based on settings)

// Format discount percentage
formatDiscount(1299, 999); // Returns "-23%"

// Format price range
formatPriceRange(99, 199); // Returns "Rs99 - Rs199"
```

**Features:**
- Uses `Intl.NumberFormat` for proper currency formatting
- Automatically reads currency from site settings
- Handles invalid currency codes gracefully with fallback
- Supports custom formatting options

#### 3.2 Updated Components

All price displays have been updated to use the dynamic currency:

1. **ProductCard.vue** - Product cards in grids
2. **ProductView.vue** - Product detail page
3. **HomeView.vue** - Home page daily deals
4. **CategoryView.vue** - Category page price filters

**Before:**
```javascript
const formatCurrency = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD', // ❌ Hardcoded to USD
        maximumFractionDigits: 0,
    }).format(value ?? 0);
```

**After:**
```javascript
import { useCurrency } from '@/composables/useCurrency';
const { formatCurrency } = useCurrency(); // ✅ Dynamic from settings (MUR/Rs)
```

### 4. Admin Settings Panel

**Route:** `/admin/settings`
**File:** `app/resources/js/views/admin/settings/SettingsView.vue`

A comprehensive settings management interface has been created with:

#### Features:
- **Currency Configuration:**
  - Select from 13 common currencies (USD, EUR, GBP, JPY, AUD, CAD, CHF, CNY, INR, BDT, PKR, AED, SAR)
  - Custom currency symbol input
  - Real-time currency preview showing formatted prices

- **Business Settings:**
  - Timezone selection
  - Business email and phone
  - Currency and currency symbol

- **Company Information:**
  - Website name
  - Tagline
  - Description

- **Live Preview:**
  - Shows three sample prices formatted with current settings
  - Updates instantly when currency changes
  - Helps visualize changes before saving

#### Navigation:
The settings page is accessible via:
- Admin sidebar under "System" section
- Direct route: `/admin/settings`
- Icon: Settings gear icon

### 5. Settings Store Integration

**File:** `app/resources/js/stores/config.js`

The config store already had currency getters that now properly read from settings:

```javascript
currency: (state) => state.settings.business?.currency || 'MUR',
currencySymbol: (state) => state.settings.business?.currency_symbol || 'Rs',
```

These are exposed through the `useSettings()` composable and used by `useCurrency()`.

## How to Use

### For Developers

1. **Displaying Prices in New Components:**

```vue
<script setup>
import { useCurrency } from '@/composables/useCurrency';

const { formatCurrency } = useCurrency();
const price = 999.99;
</script>

<template>
    <div>Price: {{ formatCurrency(price) }}</div>
</template>
```

2. **Showing Discounts:**

```vue
<script setup>
import { useCurrency } from '@/composables/useCurrency';

const { formatCurrency, formatDiscount } = useCurrency();
const originalPrice = 1299;
const salePrice = 999;
</script>

<template>
    <div>
        <span>{{ formatCurrency(salePrice) }}</span>
        <span>{{ formatCurrency(originalPrice) }}</span>
        <span>{{ formatDiscount(originalPrice, salePrice) }}</span>
    </div>
</template>
```

3. **Price Ranges:**

```vue
<script setup>
import { useCurrency } from '@/composables/useCurrency';

const { formatPriceRange } = useCurrency();
</script>

<template>
    <div>{{ formatPriceRange(99, 299) }}</div>
</template>
```

### For Administrators

1. **Changing Currency:**
   - Log in to admin panel
   - Navigate to Settings (in sidebar under "System")
   - Select desired currency from dropdown
   - Update currency symbol if needed
   - Preview the formatting in the preview section
   - Click "Save Changes"

2. **Supported Currencies:**
   - **MUR - Mauritian Rupee (Rs)** ⭐ Default
   - USD - US Dollar ($)
   - EUR - Euro (€)
   - GBP - British Pound (£)
   - JPY - Japanese Yen (¥)
   - AUD - Australian Dollar (A$)
   - CAD - Canadian Dollar (C$)
   - CHF - Swiss Franc (CHF)
   - CNY - Chinese Yuan (¥)
   - INR - Indian Rupee (₹)
   - BDT - Bangladeshi Taka (৳)
   - PKR - Pakistani Rupee (₨)
   - AED - UAE Dirham (د.إ)
   - SAR - Saudi Riyal (﷼)

3. **Effect of Changes:**
   - Changes are immediate after saving
   - All prices across the site update automatically
   - Frontend automatically fetches updated settings
   - No cache clearing required (handled automatically)

## Technical Details

### API Endpoints

- **GET** `/api/config/settings` - Public settings (including currency)
- **GET** `/admin/settings` - All settings (admin only)
- **PUT** `/admin/settings/bulk` - Bulk update settings (admin only)

### Settings Model Methods

```php
// Get a setting
Setting::get('currency', 'MUR');

// Set a setting
Setting::set('currency', 'EUR', 'business', 'string', true);

// Get all settings in a group
Setting::group('business');

// Get all public settings
Setting::getPublic();

// Clear cache (automatically called on updates)
Setting::clearCache();
```

### Cache Management

Settings are cached using Laravel's cache system:
- Cache keys: `setting:{key}`, `settings:group:{group}`, `settings:public`
- Automatic cache invalidation on updates
- Frontend refetches settings on app initialization

## Testing

To verify the implementation:

1. **Run the seeder:**
   ```bash
   php artisan db:seed --class=SettingsSeeder
   ```

2. **Check database:**
   ```bash
   php artisan tinker
   >>> Setting::where('key', 'currency')->first()
   >>> Setting::where('key', 'currency_symbol')->first()
   ```

3. **Test frontend:**
   - Visit any product page
   - Check that prices display with correct currency
   - Change currency in admin settings
   - Verify prices update accordingly

4. **Test admin panel:**
   - Go to `/admin/settings`
   - Change currency to EUR
   - Observe preview updates
   - Save changes
   - Verify frontend reflects the change

## Migration Notes

### Existing Data
All existing products and prices remain unchanged. Only the display format changes based on the currency setting.

### No Breaking Changes
The implementation is backward compatible. If settings are missing, it falls back to MUR/Rs. 

## Files Modified/Created

### Created:
- `app/resources/js/composables/useCurrency.js` - Currency formatting composable
- `app/resources/js/views/admin/settings/SettingsView.vue` - Admin settings page
- `CURRENCY_IMPLEMENTATION.md` - This documentation

### Modified:
- `app/resources/js/components/product/ProductCard.vue` - Use dynamic currency
- `app/resources/js/views/ProductView.vue` - Use dynamic currency
- `app/resources/js/views/HomeView.vue` - Use dynamic currency
- `app/resources/js/views/CategoryView.vue` - Use dynamic currency
- `app/resources/js/router/index.js` - Added settings route
- `app/resources/js/components/admin/AdminSidebar.vue` - Added settings link

### Already Existed (No Changes Needed):
- `app/database/seeders/SettingsSeeder.php` - Already had currency settings
- `app/app/Models/Setting.php` - Model with all needed methods
- `app/app/Http/Controllers/Api/ConfigController.php` - API endpoints
- `app/app/Http/Controllers/Admin/SettingController.php` - Admin API
- `app/resources/js/stores/config.js` - Already had currency getters
- `app/resources/js/composables/useSettings.js` - Already exposed currency

## Future Enhancements

Potential improvements for the future:

1. **Multiple Currency Support:**
   - Display prices in multiple currencies
   - Currency switcher for customers
   - Exchange rate integration

2. **Regional Formatting:**
   - Locale-specific number formatting
   - Decimal precision settings
   - Thousands separator customization

3. **Price Rules:**
   - Automatic price conversion
   - Regional pricing strategies
   - Tax-inclusive/exclusive settings

4. **Enhanced Admin UI:**
   - Currency converter tool
   - Historical price tracking
   - Bulk price updates with currency conversion

## Summary

✅ Currency is now fully configurable from settings
✅ All price displays use dynamic currency throughout the site
✅ Admin panel provides easy currency management
✅ Real-time preview of currency formatting
✅ Backward compatible with fallback to USD
✅ No breaking changes to existing functionality
✅ Proper caching and performance optimization

