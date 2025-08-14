<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryItemsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('inventory_items')->insert([
            [
                'category_id' => 1, // Vitamin
                'item_name' => 'Vitamin A Capsules',
                'unit' => 'Bottle (100 pcs)',
                'quantity' => 50,
                'expiry_date' => '2026-12-31',
                'created_at' => now()
            ],
            [
                'category_id' => 2, // Medicine
                'item_name' => 'Paracetamol Syrup',
                'unit' => 'Bottle (60 ml)',
                'quantity' => 30,
                'expiry_date' => '2025-10-15',
                'created_at' => now()
            ],
            [
                'category_id' => 3, // Supplement
                'item_name' => 'Iron Supplement Drops',
                'unit' => 'Bottle (30 ml)',
                'quantity' => 20,
                'expiry_date' => '2026-05-20',
                'created_at' => now()
            ]
        ]);
    }
}
