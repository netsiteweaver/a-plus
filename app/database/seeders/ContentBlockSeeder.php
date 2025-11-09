<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blocks = [
            [
                'key' => 'home_hero',
                'type' => 'hero',
                'title' => 'Hero Section',
                'page' => 'home',
                'position' => 1,
                'status' => 'published',
                'content' => [
                    'badge' => 'Featured drop',
                    'title' => 'Discover the Future of Technology',
                    'description' => 'Explore our curated selection of premium electronics, from cutting-edge laptops to smart home devices.',
                    'cta' => [
                        'label' => 'Shop now',
                        'to' => '/category/all',
                    ],
                    'image' => 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            [
                'key' => 'home_featured_categories',
                'type' => 'featured_categories',
                'title' => 'Featured Categories',
                'page' => 'home',
                'position' => 2,
                'status' => 'published',
                'content' => [
                    'eyebrow' => 'Shop by need',
                    'heading' => 'Featured categories',
                    'categories' => [
                        [
                            'slug' => 'laptops',
                            'name' => 'Laptops & Computers',
                            'description' => 'Premium ultrabooks and powerful workstations',
                            'accent' => 'Pro series',
                            'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'slug' => 'audio',
                            'name' => 'Audio & Wearables',
                            'description' => 'Immersive sound and smart fitness tracking',
                            'accent' => 'Best sellers',
                            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'slug' => 'smart-home',
                            'name' => 'Smart Home',
                            'description' => 'Connected living for modern homes',
                            'accent' => 'New arrivals',
                            'image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'home_daily_deals',
                'type' => 'daily_deals',
                'title' => 'Daily Deals',
                'page' => 'home',
                'position' => 3,
                'status' => 'published',
                'content' => [
                    'eyebrow' => 'Lightning deals',
                    'heading' => 'Daily price drops',
                    'link_text' => 'View all deals',
                    'link_url' => '/category/deals',
                    // Deals will be fetched dynamically from products
                ],
            ],
            [
                'key' => 'home_featured_products',
                'type' => 'featured_products',
                'title' => 'Featured Products',
                'page' => 'home',
                'position' => 4,
                'status' => 'published',
                'content' => [
                    'eyebrow' => 'Trending now',
                    'heading' => 'Most-loved devices',
                    'link_text' => 'See curated list',
                    'link_url' => '/category/most-loved',
                    // Products will be fetched dynamically
                ],
            ],
        ];

        foreach ($blocks as $block) {
            ContentBlock::updateOrCreate(
                ['key' => $block['key']],
                $block
            );
        }

        $this->command->info('Content blocks seeded successfully!');
    }
}
