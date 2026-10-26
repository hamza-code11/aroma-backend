<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'aroma-diffusers'],
            [
                'name' => 'Aroma Diffusers',
                'is_active' => true,
            ]
        );

        $products = [
            [
                'name' => 'Aura Essential Oil Diffuser',
                'slug' => 'aura-essential-oil-diffuser',
                'sku' => 'ISH-AD-001',
                'description' => 'A stylish aroma diffuser designed to create a relaxing atmosphere in your bedroom or living space.',
                'regular_price' => 49.99,
                'sale_price' => 39.99,
                'stock_quantity' => 25,
                'weight' => 1.2,
                'weight_unit' => 'kg',
                'length' => 18,
                'width' => 18,
                'height' => 25,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Serene Wood Grain Diffuser',
                'slug' => 'serene-wood-grain-diffuser',
                'sku' => 'ISH-AD-002',
                'description' => 'A wood-grain finish diffuser for a warm, natural and elegant home atmosphere.',
                'regular_price' => 59.99,
                'sale_price' => null,
                'stock_quantity' => 18,
                'weight' => 1.4,
                'weight_unit' => 'kg',
                'length' => 20,
                'width' => 20,
                'height' => 28,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Luxe Ceramic Aroma Diffuser',
                'slug' => 'luxe-ceramic-aroma-diffuser',
                'sku' => 'ISH-AD-003',
                'description' => 'A refined ceramic-style diffuser suited to modern interiors and bedside tables.',
                'regular_price' => 69.99,
                'sale_price' => 59.99,
                'stock_quantity' => 12,
                'weight' => 1.6,
                'weight_unit' => 'kg',
                'length' => 19,
                'width' => 19,
                'height' => 26,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Mini Mist Portable Diffuser',
                'slug' => 'mini-mist-portable-diffuser',
                'sku' => 'ISH-AD-004',
                'description' => 'A compact aroma diffuser for personal spaces, work desks and small rooms.',
                'regular_price' => 29.99,
                'sale_price' => null,
                'stock_quantity' => 30,
                'weight' => 0.5,
                'weight_unit' => 'kg',
                'length' => 12,
                'width' => 12,
                'height' => 16,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=800&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'name' => 'Zen Ultrasonic Diffuser',
                'slug' => 'zen-ultrasonic-diffuser',
                'sku' => 'ISH-AD-005',
                'description' => 'An ultrasonic diffuser for a calm and comfortable atmosphere in everyday spaces.',
                'regular_price' => 44.99,
                'sale_price' => 34.99,
                'stock_quantity' => 20,
                'weight' => 1.0,
                'weight_unit' => 'kg',
                'length' => 17,
                'width' => 17,
                'height' => 23,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'name' => 'Noir Premium Aroma Diffuser',
                'slug' => 'noir-premium-aroma-diffuser',
                'sku' => 'ISH-AD-006',
                'description' => 'A premium dark-finish diffuser designed to complement sophisticated modern interiors.',
                'regular_price' => 79.99,
                'sale_price' => 69.99,
                'stock_quantity' => 10,
                'weight' => 1.8,
                'weight_unit' => 'kg',
                'length' => 22,
                'width' => 22,
                'height' => 30,
                'dimension_unit' => 'cm',
                'main_image' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, [
                    'category_id' => $category->id,
                    'shipping_class' => 'standard',
                    'is_active' => true,
                ])
            );
        }
    }
}
