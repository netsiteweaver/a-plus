# Settings Reference Guide

## Overview

All client-specific configuration is stored in the `settings` table and accessible through the Settings API. Each setting has:
- **Key**: Unique identifier
- **Value**: JSON-encoded data
- **Group**: Category (company, legal, business, contact, social, features, branding)
- **Type**: Data type (string, number, boolean, json, text)
- **Is Public**: Whether exposed via public API
- **Description**: Human-readable explanation

## Settings by Group

### Company Settings (`company`)

#### `website_name`
- **Type**: string
- **Public**: Yes
- **Default**: "A Plus Technology"
- **Description**: Website display name shown in header, browser title, and footer
- **Usage**: Frontend branding, email templates, document headers

#### `tagline`
- **Type**: string
- **Public**: Yes
- **Default**: "Premium Electronics & Smart Solutions"
- **Description**: Company tagline/slogan
- **Usage**: Footer, About page, marketing materials

#### `description`
- **Type**: text
- **Public**: Yes
- **Default**: "High-performance devices, smart living solutions, and concierge-grade support."
- **Description**: Company description for About section and meta tags
- **Usage**: Footer, About page, SEO meta description

#### `logo_url`
- **Type**: string
- **Public**: Yes
- **Default**: "/images/logo.png"
- **Description**: Path to logo file (relative or absolute URL)
- **Usage**: Header, footer, emails, documents
- **Format**: `/images/client-name/logo.png` or `https://cdn.example.com/logo.png`

#### `favicon_url`
- **Type**: string
- **Public**: Yes
- **Default**: "/favicon.ico"
- **Description**: Path to favicon file
- **Usage**: Browser tab icon
- **Format**: `/images/client-name/favicon.ico`

---

### Legal Settings (`legal`)

#### `legal_name`
- **Type**: string
- **Public**: No
- **Default**: "A Plus Technology Ltd."
- **Description**: Official registered company name for legal documents
- **Usage**: Invoices, contracts, terms of service, legal documents

#### `registration_number`
- **Type**: string
- **Public**: No
- **Default**: ""
- **Description**: Company registration/incorporation number
- **Usage**: Invoices, legal documents, compliance

#### `tax_id`
- **Type**: string
- **Public**: No
- **Default**: ""
- **Description**: Tax identification number (VAT, EIN, etc.)
- **Usage**: Invoices, tax documents, accounting

#### `legal_address`
- **Type**: json
- **Public**: No
- **Default**: 
```json
{
  "street": "123 Business Street",
  "city": "Dhaka",
  "state": "Dhaka",
  "zip": "1000",
  "country": "Bangladesh"
}
```
- **Description**: Official registered business address
- **Usage**: Legal documents, invoices, compliance

---

### Business Settings (`business`)

#### `currency`
- **Type**: string
- **Public**: Yes
- **Default**: "USD"
- **Description**: ISO 4217 currency code
- **Usage**: Product pricing, order totals, financial reports
- **Common Values**: USD, EUR, GBP, BDT, INR, CAD, AUD

#### `currency_symbol`
- **Type**: string
- **Public**: Yes
- **Default**: "$"
- **Description**: Currency symbol for display
- **Usage**: Price formatting throughout application
- **Common Values**: $, €, £, ৳, ₹, ¥

#### `timezone`
- **Type**: string
- **Public**: Yes
- **Default**: "UTC"
- **Description**: Default timezone for date/time operations
- **Usage**: Order timestamps, scheduled tasks, reports
- **Format**: IANA timezone identifier (e.g., "America/New_York", "Asia/Dhaka")

#### `date_format`
- **Type**: string
- **Public**: Yes
- **Default**: "Y-m-d"
- **Description**: PHP date format string
- **Usage**: Displaying dates throughout application
- **Common Values**: "Y-m-d", "m/d/Y", "d/m/Y", "d-M-Y"

#### `time_format`
- **Type**: string
- **Public**: Yes
- **Default**: "H:i:s"
- **Description**: PHP time format string
- **Usage**: Displaying times throughout application
- **Common Values**: "H:i:s", "h:i A", "H:i"

#### `phone_number`
- **Type**: string
- **Public**: Yes
- **Default**: "+880 123 456 7890"
- **Description**: Primary business phone number
- **Usage**: Contact page, footer, customer communications

#### `email`
- **Type**: string
- **Public**: Yes
- **Default**: "info@aplustech.com"
- **Description**: Primary business email address
- **Usage**: Contact forms, general inquiries

#### `support_email`
- **Type**: string
- **Public**: Yes
- **Default**: "support@aplustech.com"
- **Description**: Customer support email
- **Usage**: Support tickets, help center, order issues

#### `support_phone`
- **Type**: string
- **Public**: Yes
- **Default**: "+880 123 456 7891"
- **Description**: Customer support phone number
- **Usage**: Support page, order confirmation emails

#### `tax_included`
- **Type**: boolean
- **Public**: Yes
- **Default**: false
- **Description**: Whether displayed prices include tax
- **Usage**: Price display, checkout calculations

---

### Contact Settings (`contact`)

#### `primary_address`
- **Type**: json
- **Public**: Yes
- **Default**:
```json
{
  "street": "456 Commerce Avenue",
  "city": "Dhaka",
  "state": "Dhaka",
  "zip": "1200",
  "country": "Bangladesh"
}
```
- **Description**: Main business/storefront address
- **Usage**: Contact page, footer, Google Maps integration

#### `business_hours`
- **Type**: json
- **Public**: Yes
- **Default**:
```json
{
  "monday": "9:00 AM - 6:00 PM",
  "tuesday": "9:00 AM - 6:00 PM",
  "wednesday": "9:00 AM - 6:00 PM",
  "thursday": "9:00 AM - 6:00 PM",
  "friday": "9:00 AM - 6:00 PM",
  "saturday": "10:00 AM - 4:00 PM",
  "sunday": "Closed"
}
```
- **Description**: Operating hours by day of week
- **Usage**: Contact page, support hours display

---

### Social Media Settings (`social`)

#### `facebook`
- **Type**: string
- **Public**: Yes
- **Default**: ""
- **Description**: Facebook page URL
- **Usage**: Footer social links, share buttons
- **Format**: "https://facebook.com/company-name"

#### `twitter`
- **Type**: string
- **Public**: Yes
- **Default**: ""
- **Description**: Twitter/X profile URL
- **Usage**: Footer social links, share buttons
- **Format**: "https://twitter.com/company_name"

#### `instagram`
- **Type**: string
- **Public**: Yes
- **Default**: ""
- **Description**: Instagram profile URL
- **Usage**: Footer social links
- **Format**: "https://instagram.com/company_name"

#### `linkedin`
- **Type**: string
- **Public**: Yes
- **Default**: ""
- **Description**: LinkedIn company page URL
- **Usage**: Footer social links, B2B communications
- **Format**: "https://linkedin.com/company/company-name"

#### `youtube`
- **Type**: string
- **Public**: Yes
- **Default**: ""
- **Description**: YouTube channel URL
- **Usage**: Footer social links, video embeds
- **Format**: "https://youtube.com/@company-name"

---

### Feature Toggles (`features`)

#### `enable_reviews`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Enable product review functionality
- **Usage**: Product pages, review submission forms

#### `enable_wishlist`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Enable wishlist/favorites feature
- **Usage**: Product cards, user account section

#### `enable_comparison`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Enable product comparison tool
- **Usage**: Product listing pages, comparison page

#### `enable_loyalty`
- **Type**: boolean
- **Public**: Yes
- **Default**: false
- **Description**: Enable loyalty points/rewards program
- **Usage**: Account pages, checkout, marketing emails

#### `enable_gift_cards`
- **Type**: boolean
- **Public**: Yes
- **Default**: false
- **Description**: Enable gift card purchase and redemption
- **Usage**: Shop navigation, cart, checkout

#### `enable_quick_view`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Enable quick view modal on product cards
- **Usage**: Product listing pages, search results

#### `enable_newsletter`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Enable newsletter subscription form
- **Usage**: Footer, popup modals, account preferences

---

### Branding Settings (`branding`)

#### `primary_color`
- **Type**: string
- **Public**: Yes
- **Default**: "#0ea5e9"
- **Description**: Primary brand color (hex code)
- **Usage**: Buttons, links, accents, CTA elements
- **Format**: "#RRGGBB" (e.g., "#0ea5e9")

#### `secondary_color`
- **Type**: string
- **Public**: Yes
- **Default**: "#64748b"
- **Description**: Secondary brand color (hex code)
- **Usage**: Headings, secondary buttons, borders
- **Format**: "#RRGGBB" (e.g., "#64748b")

#### `show_tagline`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Display company tagline in header/footer
- **Usage**: Header subtitle, footer branding

#### `show_promo_banner`
- **Type**: boolean
- **Public**: Yes
- **Default**: true
- **Description**: Display promotional banner at top of page
- **Usage**: Header top bar

#### `promo_banner_text`
- **Type**: string
- **Public**: Yes
- **Default**: "Next-day delivery in selected cities."
- **Description**: Text content for promotional banner
- **Usage**: Header promo banner

---

## Updating Settings

### Via Admin API

```javascript
// Update single setting
await axios.put('/api/admin/settings/{settingId}', {
  value: 'new value'
});

// Bulk update
await axios.put('/api/admin/settings/bulk', {
  settings: [
    { key: 'website_name', value: 'New Company Name' },
    { key: 'primary_color', value: '#ff6b6b' }
  ]
});
```

### Via Laravel Code

```php
use App\Models\Setting;

// Get setting
$websiteName = Setting::get('website_name', 'Default Name');

// Set setting
Setting::set('website_name', 'New Company Name', 'company', 'string', true);

// Get all settings in group
$companySettings = Setting::group('company');

// Clear cache after updates
Setting::clearCache();
```

## Frontend Usage

### Via Composable

```javascript
import { useSettings } from '@/composables/useSettings';

const {
  companyName,
  primaryColor,
  isFeatureEnabled
} = useSettings();

// Check feature flag
if (isFeatureEnabled('reviews')) {
  // Show reviews section
}
```

### Via Store

```javascript
import { useConfigStore } from '@/stores/config';

const config = useConfigStore();

// Access settings
const name = config.companyName;
const logo = config.companyLogo;
```

## Validation Rules

When creating/updating settings:

- **Key**: Required, unique, alphanumeric with underscores
- **Value**: Required, must match type
- **Group**: Required, one of: company, legal, business, contact, social, features, branding
- **Type**: Required, one of: string, number, boolean, json, text
- **Is Public**: Boolean, defaults to false
- **Description**: Optional, text description

## Best Practices

1. **Naming Convention**: Use snake_case for keys (e.g., `website_name`, `enable_reviews`)
2. **Public Settings**: Only mark settings as public if needed by frontend
3. **Sensitive Data**: Never mark sensitive data (tax_id, credentials) as public
4. **JSON Validation**: Validate JSON structure before saving json-type settings
5. **Cache Management**: Always clear cache after bulk updates
6. **Defaults**: Provide sensible defaults in seeders for new deployments
7. **Documentation**: Document custom settings added for specific clients

