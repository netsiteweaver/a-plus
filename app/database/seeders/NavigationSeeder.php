<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use App\Models\NavigationItem;
use App\Models\NavigationMegaColumn;
use App\Models\NavigationMegaItem;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Primary Menu
        $primaryMenu = NavigationMenu::create([
            'name' => 'Primary Navigation',
            'location' => 'primary',
            'description' => 'Main navigation menu',
            'is_active' => true,
        ]);

        // Home
        NavigationItem::create([
            'menu_id' => $primaryMenu->id,
            'label' => 'Home',
            'url' => '/',
            'position' => 1,
            'is_active' => true,
        ]);

        // Laptops & Computers (with mega menu)
        $laptopsItem = NavigationItem::create([
            'menu_id' => $primaryMenu->id,
            'label' => 'Laptops & Computers',
            'url' => '/category/laptops',
            'description' => 'Premium ultrabooks, gaming rigs, and workstations.',
            'is_mega' => true,
            'position' => 2,
            'is_active' => true,
            'hero' => [
                'eyebrow' => 'Save up to 25%',
                'title' => 'Vision Pro Ultrabook 16',
                'description' => '12th gen Intel H-series, OLED 240Hz, LiquidCool X2 thermal design.',
                'to' => '/product/vision-pro-ultrabook-16',
                'image' => 'https://images.unsplash.com/photo-1527430253228-e93688616381?auto=format&fit=crop&w=900&q=80',
            ],
        ]);

        // Laptops columns
        $laptopsCol1 = NavigationMegaColumn::create([
            'navigation_item_id' => $laptopsItem->id,
            'heading' => 'Shop by device',
            'position' => 1,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol1->id, 'label' => 'Ultrabooks', 'url' => '/category/ultrabooks', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol1->id, 'label' => 'Gaming laptops', 'url' => '/category/gaming-laptops', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol1->id, 'label' => '2-in-1 convertibles', 'url' => '/category/2-in-1', 'position' => 3]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol1->id, 'label' => 'Chromebooks', 'url' => '/category/chromebooks', 'position' => 4]);

        $laptopsCol2 = NavigationMegaColumn::create([
            'navigation_item_id' => $laptopsItem->id,
            'heading' => 'Shop by brand',
            'position' => 2,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol2->id, 'label' => 'Apple', 'url' => '/category/laptops?brand=apple', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol2->id, 'label' => 'Lenovo', 'url' => '/category/laptops?brand=lenovo', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol2->id, 'label' => 'ASUS', 'url' => '/category/laptops?brand=asus', 'position' => 3]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol2->id, 'label' => 'MSI', 'url' => '/category/laptops?brand=msi', 'position' => 4]);

        $laptopsCol3 = NavigationMegaColumn::create([
            'navigation_item_id' => $laptopsItem->id,
            'heading' => 'Featured',
            'position' => 3,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol3->id, 'label' => 'Creator studio picks', 'url' => '/category/creator-laptops', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol3->id, 'label' => 'AI-ready PCs', 'url' => '/category/ai-pc', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $laptopsCol3->id, 'label' => 'Certified refurbished', 'url' => '/category/refurbished', 'position' => 3]);

        // Audio & Wearables (with mega menu)
        $audioItem = NavigationItem::create([
            'menu_id' => $primaryMenu->id,
            'label' => 'Audio & Wearables',
            'url' => '/category/audio',
            'description' => 'Immersive listening, crystal-clear calls, fitness tracking.',
            'is_mega' => true,
            'position' => 3,
            'is_active' => true,
            'hero' => [
                'eyebrow' => 'Bundle & save',
                'title' => 'Nimbus Soundstage Pro',
                'description' => 'Spatial audio earbuds with adaptive noise control and 48-hour battery.',
                'to' => '/product/nimbus-soundstage-pro',
                'image' => 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?auto=format&fit=crop&w=900&q=80',
            ],
        ]);

        // Audio columns
        $audioCol1 = NavigationMegaColumn::create([
            'navigation_item_id' => $audioItem->id,
            'heading' => 'Headphones',
            'position' => 1,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $audioCol1->id, 'label' => 'Noise-cancelling', 'url' => '/category/noise-cancelling', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol1->id, 'label' => 'True wireless', 'url' => '/category/true-wireless', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol1->id, 'label' => 'Studio monitors', 'url' => '/category/studio-headphones', 'position' => 3]);

        $audioCol2 = NavigationMegaColumn::create([
            'navigation_item_id' => $audioItem->id,
            'heading' => 'Wearables',
            'position' => 2,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $audioCol2->id, 'label' => 'Smartwatches', 'url' => '/category/smartwatches', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol2->id, 'label' => 'Fitness trackers', 'url' => '/category/fitness-trackers', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol2->id, 'label' => 'AR glasses', 'url' => '/category/ar-glasses', 'position' => 3]);

        $audioCol3 = NavigationMegaColumn::create([
            'navigation_item_id' => $audioItem->id,
            'heading' => 'Accessories',
            'position' => 3,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $audioCol3->id, 'label' => 'DAC & amps', 'url' => '/category/audio-dac', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol3->id, 'label' => 'Microphones', 'url' => '/category/microphones', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $audioCol3->id, 'label' => 'Charging stations', 'url' => '/category/charging', 'position' => 3]);

        // Smart Home (with mega menu)
        $smartHomeItem = NavigationItem::create([
            'menu_id' => $primaryMenu->id,
            'label' => 'Smart Home',
            'url' => '/category/smart-home',
            'description' => 'Automate lighting, climate, security, and more.',
            'is_mega' => true,
            'position' => 4,
            'is_active' => true,
            'hero' => [
                'eyebrow' => 'Smart essentials',
                'title' => 'HomeSphere Automation Hub X2',
                'description' => 'Control 300+ devices with Matter, Thread, and Zigbee support.',
                'to' => '/product/homesphere-automation-hub-x2',
                'image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=900&q=80',
            ],
        ]);

        // Smart Home columns
        $smartCol1 = NavigationMegaColumn::create([
            'navigation_item_id' => $smartHomeItem->id,
            'heading' => 'Solutions',
            'position' => 1,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $smartCol1->id, 'label' => 'Smart lighting', 'url' => '/category/smart-lighting', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol1->id, 'label' => 'Climate control', 'url' => '/category/climate', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol1->id, 'label' => 'Security & cams', 'url' => '/category/security', 'position' => 3]);

        $smartCol2 = NavigationMegaColumn::create([
            'navigation_item_id' => $smartHomeItem->id,
            'heading' => 'Ecosystems',
            'position' => 2,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $smartCol2->id, 'label' => 'Matter-ready', 'url' => '/category/matter', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol2->id, 'label' => 'HomeKit', 'url' => '/category/homekit', 'position' => 2]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol2->id, 'label' => 'Google Home', 'url' => '/category/google-home', 'position' => 3]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol2->id, 'label' => 'Alexa', 'url' => '/category/alexa', 'position' => 4]);

        $smartCol3 = NavigationMegaColumn::create([
            'navigation_item_id' => $smartHomeItem->id,
            'heading' => 'Services',
            'position' => 3,
        ]);
        
        NavigationMegaItem::create(['mega_column_id' => $smartCol3->id, 'label' => 'Installation', 'url' => '/services/installation', 'position' => 1]);
        NavigationMegaItem::create(['mega_column_id' => $smartCol3->id, 'label' => 'Pro monitoring', 'url' => '/services/monitoring', 'position' => 2]);

        // Services
        NavigationItem::create([
            'menu_id' => $primaryMenu->id,
            'label' => 'Services',
            'url' => '/services',
            'position' => 5,
            'is_active' => true,
        ]);

        // Create Utility Menu
        $utilityMenu = NavigationMenu::create([
            'name' => 'Utility Navigation',
            'location' => 'utility',
            'description' => 'Top bar utility links',
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $utilityMenu->id,
            'label' => 'Support',
            'url' => '/support',
            'position' => 1,
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $utilityMenu->id,
            'label' => 'Track order',
            'url' => '/support/track-order',
            'position' => 2,
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $utilityMenu->id,
            'label' => 'Business',
            'url' => '/business',
            'position' => 3,
            'is_active' => true,
        ]);

        // Create Footer Menu
        $footerMenu = NavigationMenu::create([
            'name' => 'Footer Navigation',
            'location' => 'footer',
            'description' => 'Footer links',
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $footerMenu->id,
            'label' => 'About',
            'url' => '/about',
            'position' => 1,
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $footerMenu->id,
            'label' => 'Contact',
            'url' => '/contact',
            'position' => 2,
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $footerMenu->id,
            'label' => 'Privacy Policy',
            'url' => '/privacy',
            'position' => 3,
            'is_active' => true,
        ]);

        NavigationItem::create([
            'menu_id' => $footerMenu->id,
            'label' => 'Terms of Service',
            'url' => '/terms',
            'position' => 4,
            'is_active' => true,
        ]);

        $this->command->info('Navigation seeded successfully!');
    }
}
