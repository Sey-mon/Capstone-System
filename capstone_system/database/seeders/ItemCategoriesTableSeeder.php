<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemCategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('item_categories')->insert([
            ['category_name' => 'Vitamin'],
            ['category_name' => 'Medicine'],
            ['category_name' => 'Supplement']
        ]);
    }
}
