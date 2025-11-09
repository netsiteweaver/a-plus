<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Company Settings
            ['key' => 'website_name', 'value' => ['A Plus Technology'], 'group' => 'company', 'type' => 'string', 'is_public' => true, 'description' => 'Website display name'],
            ['key' => 'tagline', 'value' => ['Premium Electronics & Smart Solutions'], 'group' => 'company', 'type' => 'string', 'is_public' => true, 'description' => 'Company tagline'],
            ['key' => 'description', 'value' => ['High-performance devices, smart living solutions, and concierge-grade support.'], 'group' => 'company', 'type' => 'text', 'is_public' => true, 'description' => 'Company description'],
            ['key' => 'logo_url', 'value' => ['/images/logo.png'], 'group' => 'company', 'type' => 'string', 'is_public' => true, 'description' => 'Logo file path or URL'],
            ['key' => 'favicon_url', 'value' => ['/favicon.ico'], 'group' => 'company', 'type' => 'string', 'is_public' => true, 'description' => 'Favicon file path or URL'],

            // Legal Settings
            ['key' => 'legal_name', 'value' => ['A Plus Technology Ltd.'], 'group' => 'legal', 'type' => 'string', 'is_public' => false, 'description' => 'Legal company name for documents'],
            ['key' => 'registration_number', 'value' => [''], 'group' => 'legal', 'type' => 'string', 'is_public' => false, 'description' => 'Company registration number'],
            ['key' => 'tax_id', 'value' => [''], 'group' => 'legal', 'type' => 'string', 'is_public' => false, 'description' => 'Tax identification number'],
            ['key' => 'legal_address', 'value' => [
                [
                    'street' => '123 Business Street',
                    'city' => 'Dhaka',
                    'state' => 'Dhaka',
                    'zip' => '1000',
                    'country' => 'Bangladesh'
                ]
            ], 'group' => 'legal', 'type' => 'json', 'is_public' => false, 'description' => 'Legal registered address'],

            // Business Settings
            ['key' => 'currency', 'value' => ['USD'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Default currency code'],
            ['key' => 'currency_symbol', 'value' => ['$'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Currency symbol'],
            ['key' => 'timezone', 'value' => ['UTC'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Default timezone'],
            ['key' => 'date_format', 'value' => ['Y-m-d'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Date format'],
            ['key' => 'time_format', 'value' => ['H:i:s'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Time format'],
            ['key' => 'phone_number', 'value' => ['+880 123 456 7890'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Primary phone number'],
            ['key' => 'email', 'value' => ['info@aplustech.com'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Primary email address'],
            ['key' => 'support_email', 'value' => ['support@aplustech.com'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Support email address'],
            ['key' => 'support_phone', 'value' => ['+880 123 456 7891'], 'group' => 'business', 'type' => 'string', 'is_public' => true, 'description' => 'Support phone number'],
            ['key' => 'tax_included', 'value' => [false], 'group' => 'business', 'type' => 'boolean', 'is_public' => true, 'description' => 'Whether prices include tax'],

            // Contact Settings
            ['key' => 'primary_address', 'value' => [
                [
                    'street' => '456 Commerce Avenue',
                    'city' => 'Dhaka',
                    'state' => 'Dhaka',
                    'zip' => '1200',
                    'country' => 'Bangladesh'
                ]
            ], 'group' => 'contact', 'type' => 'json', 'is_public' => true, 'description' => 'Primary business address'],
            ['key' => 'business_hours', 'value' => [
                [
                    'monday' => '9:00 AM - 6:00 PM',
                    'tuesday' => '9:00 AM - 6:00 PM',
                    'wednesday' => '9:00 AM - 6:00 PM',
                    'thursday' => '9:00 AM - 6:00 PM',
                    'friday' => '9:00 AM - 6:00 PM',
                    'saturday' => '10:00 AM - 4:00 PM',
                    'sunday' => 'Closed'
                ]
            ], 'group' => 'contact', 'type' => 'json', 'is_public' => true, 'description' => 'Business operating hours'],

            // Social Media Settings
            ['key' => 'facebook', 'value' => [''], 'group' => 'social', 'type' => 'string', 'is_public' => true, 'description' => 'Facebook page URL'],
            ['key' => 'twitter', 'value' => [''], 'group' => 'social', 'type' => 'string', 'is_public' => true, 'description' => 'Twitter/X profile URL'],
            ['key' => 'instagram', 'value' => [''], 'group' => 'social', 'type' => 'string', 'is_public' => true, 'description' => 'Instagram profile URL'],
            ['key' => 'linkedin', 'value' => [''], 'group' => 'social', 'type' => 'string', 'is_public' => true, 'description' => 'LinkedIn company page URL'],
            ['key' => 'youtube', 'value' => [''], 'group' => 'social', 'type' => 'string', 'is_public' => true, 'description' => 'YouTube channel URL'],

            // Feature Toggles
            ['key' => 'enable_reviews', 'value' => [true], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable product reviews'],
            ['key' => 'enable_wishlist', 'value' => [true], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable wishlist functionality'],
            ['key' => 'enable_comparison', 'value' => [true], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable product comparison'],
            ['key' => 'enable_loyalty', 'value' => [false], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable loyalty program'],
            ['key' => 'enable_gift_cards', 'value' => [false], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable gift cards'],
            ['key' => 'enable_quick_view', 'value' => [true], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable product quick view'],
            ['key' => 'enable_newsletter', 'value' => [true], 'group' => 'features', 'type' => 'boolean', 'is_public' => true, 'description' => 'Enable newsletter subscription'],

            // Branding Settings
            ['key' => 'primary_color', 'value' => ['#0ea5e9'], 'group' => 'branding', 'type' => 'string', 'is_public' => true, 'description' => 'Primary brand color'],
            ['key' => 'secondary_color', 'value' => ['#64748b'], 'group' => 'branding', 'type' => 'string', 'is_public' => true, 'description' => 'Secondary brand color'],
            ['key' => 'show_tagline', 'value' => [true], 'group' => 'branding', 'type' => 'boolean', 'is_public' => true, 'description' => 'Show tagline in header'],
            ['key' => 'show_promo_banner', 'value' => [true], 'group' => 'branding', 'type' => 'boolean', 'is_public' => true, 'description' => 'Show promotional banner'],
            ['key' => 'promo_banner_text', 'value' => ['Next-day delivery in selected cities.'], 'group' => 'branding', 'type' => 'string', 'is_public' => true, 'description' => 'Promotional banner text'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully!');
    }
}
