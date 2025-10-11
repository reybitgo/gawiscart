<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\UnilevelSetting;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Premium Herbal Tea',
                'price' => 500.00,
                'points_awarded' => 10,
                'category' => 'Beverages',
                'short_description' => 'A refreshing blend of premium herbs for health and wellness',
                'long_description' => 'Our Premium Herbal Tea is carefully crafted from the finest organic herbs, designed to promote relaxation, boost immunity, and support overall wellness. Each sachet contains a perfect blend of chamomile, peppermint, and ginger.',
                'quantity_available' => 100,
                'weight_grams' => 250,
                'sort_order' => 1,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 20.00],
                    ['level' => 2, 'bonus_amount' => 10.00],
                    ['level' => 3, 'bonus_amount' => 10.00],
                    ['level' => 4, 'bonus_amount' => 10.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ],
            ],
            [
                'name' => 'Vitamin C+ Complex',
                'price' => 800.00,
                'points_awarded' => 15,
                'category' => 'Vitamins',
                'short_description' => 'High-potency vitamin C with bioflavonoids for immune support',
                'long_description' => 'Boost your immune system with our Vitamin C+ Complex. Each capsule contains 1000mg of vitamin C plus bioflavonoids and rose hips for enhanced absorption and effectiveness. Perfect for daily immune support.',
                'quantity_available' => 150,
                'weight_grams' => 120,
                'sort_order' => 2,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 30.00],
                    ['level' => 2, 'bonus_amount' => 15.00],
                    ['level' => 3, 'bonus_amount' => 15.00],
                    ['level' => 4, 'bonus_amount' => 10.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ],
            ],
            [
                'name' => 'Omega-3 Fish Oil',
                'price' => 1200.00,
                'points_awarded' => 20,
                'category' => 'Supplements',
                'short_description' => 'Pure omega-3 fatty acids for heart and brain health',
                'long_description' => 'Our premium Omega-3 Fish Oil is sourced from wild-caught fish and purified to remove all contaminants. Rich in EPA and DHA, it supports cardiovascular health, brain function, and joint mobility.',
                'quantity_available' => 75,
                'weight_grams' => 180,
                'sort_order' => 3,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 50.00],
                    ['level' => 2, 'bonus_amount' => 20.00],
                    ['level' => 3, 'bonus_amount' => 15.00],
                    ['level' => 4, 'bonus_amount' => 15.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ],
            ],
            [
                'name' => 'Collagen Beauty Drink',
                'price' => 1500.00,
                'points_awarded' => 25,
                'category' => 'Beauty',
                'short_description' => 'Marine collagen peptides for youthful skin and strong nails',
                'long_description' => 'Transform your beauty routine with our Collagen Beauty Drink. Made from marine collagen peptides with added vitamins C and E, this delicious drink supports skin elasticity, reduces fine lines, and strengthens hair and nails.',
                'quantity_available' => 50,
                'weight_grams' => 300,
                'sort_order' => 4,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 60.00],
                    ['level' => 2, 'bonus_amount' => 25.00],
                    ['level' => 3, 'bonus_amount' => 20.00],
                    ['level' => 4, 'bonus_amount' => 15.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ],
            ],
            [
                'name' => 'Probiotic Plus',
                'price' => 950.00,
                'points_awarded' => 18,
                'category' => 'Digestive Health',
                'short_description' => '10 billion CFU probiotics for optimal gut health',
                'long_description' => 'Support your digestive system with Probiotic Plus. Our advanced formula contains 10 billion CFU of beneficial bacteria strains that promote gut health, enhance nutrient absorption, and boost immune function.',
                'quantity_available' => 120,
                'weight_grams' => 90,
                'sort_order' => 5,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 40.00],
                    ['level' => 2, 'bonus_amount' => 15.00],
                    ['level' => 3, 'bonus_amount' => 15.00],
                    ['level' => 4, 'bonus_amount' => 10.00],
                    ['level' => 5, 'bonus_amount' => 10.00],
                ],
            ],
            [
                'name' => 'Alkaline Water (12 Bottles)',
                'price' => 600.00,
                'points_awarded' => 12,
                'category' => 'Beverages',
                'short_description' => 'pH-balanced alkaline water for optimal hydration',
                'long_description' => 'Stay hydrated with our premium Alkaline Water. Each case contains 12 bottles of pH-balanced water (pH 9.5+) enriched with essential minerals. Perfect for daily hydration and maintaining optimal body pH.',
                'quantity_available' => null, // Unlimited
                'weight_grams' => 6000,
                'sort_order' => 6,
                'is_active' => true,
                'unilevel_structure' => [
                    ['level' => 1, 'bonus_amount' => 25.00],
                    ['level' => 2, 'bonus_amount' => 10.00],
                    ['level' => 3, 'bonus_amount' => 10.00],
                    ['level' => 4, 'bonus_amount' => 5.00],
                    ['level' => 5, 'bonus_amount' => 5.00],
                ],
            ],
        ];

        foreach ($products as $productData) {
            // Extract unilevel structure before creating product
            $unilevelStructure = $productData['unilevel_structure'];
            unset($productData['unilevel_structure']);

            // Create product
            $product = Product::create($productData);

            // Create unilevel settings for this product
            foreach ($unilevelStructure as $setting) {
                UnilevelSetting::create([
                    'product_id' => $product->id,
                    'level' => $setting['level'],
                    'bonus_amount' => $setting['bonus_amount'],
                    'is_active' => true,
                ]);
            }

            // Update cached total unilevel bonus
            $product->updateTotalUnilevelBonus();
        }

        $this->command->info('Created ' . count($products) . ' products with unilevel settings.');
    }
}
