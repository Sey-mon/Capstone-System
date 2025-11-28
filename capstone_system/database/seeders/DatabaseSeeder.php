<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,
            BarangaysTableSeeder::class,
            ItemCategoriesTableSeeder::class,
            FoodsTableSeeder::class,  // Added Filipino foods seeder
            UsersTableSeeder::class,
            PatientTableSeeder::class,
            AssessmentsTableSeeder::class,
            InventoryItemsTableSeeder::class,
        ]);
    }
}
