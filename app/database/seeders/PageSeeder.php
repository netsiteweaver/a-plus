<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'About Us',
                'content' => '<h1>About A Plus Technology</h1><p>We are dedicated to bringing you the finest selection of electronics and smart home solutions.</p>',
                'meta_title' => 'About Us - A Plus Technology',
                'meta_description' => 'Learn more about A Plus Technology and our commitment to quality electronics.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
            [
                'slug' => 'services',
                'title' => 'Our Services',
                'content' => '<h1>Services</h1><p>Professional installation, setup, and support for all your technology needs.</p>',
                'meta_title' => 'Services - A Plus Technology',
                'meta_description' => 'Explore our comprehensive technology services including installation and support.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
            [
                'slug' => 'support',
                'title' => 'Customer Support',
                'content' => '<h1>Support Center</h1><p>Get help with your products, orders, and more.</p>',
                'meta_title' => 'Support - A Plus Technology',
                'meta_description' => 'Access our support center for help with products, orders, and technical assistance.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information.</p>',
                'meta_title' => 'Privacy Policy - A Plus Technology',
                'meta_description' => 'Read our privacy policy to understand how we handle your personal information.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'content' => '<h1>Terms of Service</h1><p>By using our website and services, you agree to these terms and conditions.</p>',
                'meta_title' => 'Terms of Service - A Plus Technology',
                'meta_description' => 'Review our terms of service and conditions of use.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact Us',
                'content' => '<h1>Contact Us</h1><p>Get in touch with our team for any inquiries or support.</p>',
                'meta_title' => 'Contact Us - A Plus Technology',
                'meta_description' => 'Contact A Plus Technology for inquiries, support, or feedback.',
                'status' => 'published',
                'template' => 'default',
                'is_system' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }

        $this->command->info('Pages seeded successfully!');
    }
}
