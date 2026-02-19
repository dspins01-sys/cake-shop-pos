<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID categories
        $cakeId = DB::table('categories')->where('slug', 'cakes')->first()->id ?? 1;
        $cookiesId = DB::table('categories')->where('slug', 'cookies')->first()->id ?? 2;
        $pastriesId = DB::table('categories')->where('slug', 'pastries')->first()->id ?? 3;
        $cupcakesId = DB::table('categories')->where('slug', 'cupcakes')->first()->id ?? 4;

        $products = [
            // Cakes
            [
                'name' => 'Chocolate Fudge Cake',
                'slug' => 'chocolate-fudge-cake',
                'description' => 'Rich chocolate cake with fudge icing',
                'price' => 250000,
                'stock' => 15,
                'is_active' => true,
                'category_id' => $cakeId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Red Velvet Cake',
                'slug' => 'red-velvet-cake',
                'description' => 'Classic red velvet with cream cheese frosting',
                'price' => 275000,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $cakeId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Cookies
            [
                'name' => 'Chocolate Chip Cookies',
                'slug' => 'chocolate-chip-cookies',
                'description' => 'Classic cookies with chocolate chips (250g)',
                'price' => 75000,
                'stock' => 50,
                'is_active' => true,
                'category_id' => $cookiesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Butter Cookies',
                'slug' => 'butter-cookies',
                'description' => 'Melt-in-your-mouth butter cookies (250g)',
                'price' => 85000,
                'stock' => 40,
                'is_active' => true,
                'category_id' => $cookiesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Cupcakes
            [
                'name' => 'Vanilla Cupcakes',
                'slug' => 'vanilla-cupcakes',
                'description' => 'Light vanilla cupcakes with buttercream (box of 6)',
                'price' => 120000,
                'stock' => 25,
                'is_active' => true,
                'category_id' => $cupcakesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Chocolate Cupcakes',
                'slug' => 'chocolate-cupcakes',
                'description' => 'Rich chocolate cupcakes with ganache (box of 6)',
                'price' => 135000,
                'stock' => 25,
                'is_active' => true,
                'category_id' => $cupcakesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Pastries
            [
                'name' => 'Croissant',
                'slug' => 'croissant',
                'description' => 'Buttery, flaky French croissant',
                'price' => 25000,
                'stock' => 30,
                'is_active' => true,
                'category_id' => $pastriesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Danish Pastry',
                'slug' => 'danish-pastry',
                'description' => 'Filled with cream cheese and fruit',
                'price' => 30000,
                'stock' => 25,
                'is_active' => true,
                'category_id' => $pastriesId,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert($product);
        }
        
        $this->command->info('Products seeded successfully!');
    }
}
