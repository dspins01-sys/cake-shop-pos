<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cakes',
                'slug' => 'cakes',
                'description' => 'Delicious cakes for all occasions',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cookies',
                'slug' => 'cookies',
                'description' => 'Crunchy and tasty cookies',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pastries',
                'slug' => 'pastries',
                'description' => 'Fresh baked pastries daily',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Cupcakes',
                'slug' => 'cupcakes',
                'description' => 'Perfect individual sized cakes',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert($category);
        }
        
        $this->command->info('Categories seeded successfully!');
    }
}
