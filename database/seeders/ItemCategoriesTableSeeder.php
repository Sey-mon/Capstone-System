<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategoriesTableSeeder extends Seeder
{
    public function run()
    {
            $categories = [
                'Vitamin',
                'Medicine',
                'Supplement',
                'Food',
                'Equipment',
                'Medical Supply',
                'Personal Care',
                'Sanitation',
                'Infant Formula',
                'First Aid',
                'Disinfectant',
                'Protective Gear',
                'Diagnostic Tool',
                'Nutritional Product',
                'Consumable',
                'Other'
            ];

            $data = array_map(fn($name) => ['category_name' => $name], $categories);
            DB::table('item_categories')->upsert($data, ['category_name'], ['category_name']);
    }
}
