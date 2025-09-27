<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Package',
                'price' => 9.99,
                'points_awarded' => 100,
                'quantity_available' => 50,
                'short_description' => 'Perfect for beginners - includes basic features and 100 points',
                'long_description' => 'The Starter Package is designed for new users who want to explore our platform. It includes access to basic features, 100 reward points, and priority customer support. This package is ideal for individuals looking to get started with our services without a large upfront investment.',
                'is_active' => true,
                'sort_order' => 1,
                'meta_data' => [
                    'features' => ['Basic Access', 'Email Support', '100 Points'],
                    'duration' => '30 days',
                    'category' => 'basic'
                ]
            ],
            [
                'name' => 'Professional Package',
                'price' => 29.99,
                'points_awarded' => 350,
                'quantity_available' => 30,
                'short_description' => 'Enhanced features for professionals with 350 points and priority support',
                'long_description' => 'The Professional Package offers advanced features for serious users. Includes premium tools, 350 reward points, priority support, and access to exclusive content. Perfect for professionals who need reliable and comprehensive service.',
                'is_active' => true,
                'sort_order' => 2,
                'meta_data' => [
                    'features' => ['Advanced Access', 'Priority Support', '350 Points', 'Premium Tools'],
                    'duration' => '60 days',
                    'category' => 'professional'
                ]
            ],
            [
                'name' => 'Enterprise Package',
                'price' => 99.99,
                'points_awarded' => 1200,
                'quantity_available' => 10,
                'short_description' => 'Complete enterprise solution with 1200 points and dedicated support',
                'long_description' => 'Our most comprehensive package designed for enterprises and power users. Includes all premium features, 1200 reward points, dedicated account manager, custom integrations, and 24/7 support. Perfect for organizations requiring maximum functionality and support.',
                'is_active' => true,
                'sort_order' => 3,
                'meta_data' => [
                    'features' => ['Full Access', 'Dedicated Support', '1200 Points', 'Custom Integrations', '24/7 Support'],
                    'duration' => '365 days',
                    'category' => 'enterprise'
                ]
            ],
            [
                'name' => 'Limited Edition Package',
                'price' => 49.99,
                'points_awarded' => 600,
                'quantity_available' => 5,
                'short_description' => 'Exclusive limited edition package with special perks and 600 points',
                'long_description' => 'A special limited edition package available for a short time only. Includes unique features not available elsewhere, 600 reward points, exclusive badges, and special recognition in our community. Only 5 packages available!',
                'is_active' => true,
                'sort_order' => 4,
                'meta_data' => [
                    'features' => ['Exclusive Access', 'Special Badge', '600 Points', 'Limited Edition'],
                    'duration' => '90 days',
                    'category' => 'limited',
                    'badge' => 'Limited Edition'
                ]
            ],
            [
                'name' => 'Digital Nomad Package',
                'price' => 19.99,
                'points_awarded' => 200,
                'quantity_available' => null,
                'short_description' => 'Perfect for remote workers with flexible features and 200 points',
                'long_description' => 'Designed specifically for digital nomads and remote workers. Includes mobile-optimized features, cross-device synchronization, 200 reward points, and location-independent access. Unlimited availability for the modern workforce.',
                'is_active' => true,
                'sort_order' => 5,
                'meta_data' => [
                    'features' => ['Mobile Optimized', 'Cross-device Sync', '200 Points', 'Global Access'],
                    'duration' => '45 days',
                    'category' => 'nomad'
                ]
            ]
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
        }
    }
}
