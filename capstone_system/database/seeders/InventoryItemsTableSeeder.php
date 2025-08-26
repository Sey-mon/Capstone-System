<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryItemsTableSeeder extends Seeder
{
    public function run()
    {
            // Map category names to IDs (ensure these match your actual DB)
            $categories = [
                'Vitamin' => 1,
                'Medicine' => 2,
                'Supplement' => 3,
                'Food' => 4,
                'Equipment' => 5,
                'Medical Supply' => 6,
                'Personal Care' => 7,
                'Sanitation' => 8,
                'Infant Formula' => 9,
                'First Aid' => 10,
                'Disinfectant' => 11,
                'Protective Gear' => 12,
                'Diagnostic Tool' => 13,
                'Nutritional Product' => 14,
                'Consumable' => 15,
                'Other' => 16
            ];

            $items = [
                [
                    'category_id' => $categories['Vitamin'],
                    'item_name' => 'Vitamin A Capsules',
                    'unit' => 'Bottle (100 pcs)',
                    'quantity' => 50,
                    'expiry_date' => '2026-12-31',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Medicine'],
                    'item_name' => 'Paracetamol Syrup',
                    'unit' => 'Bottle (60 ml)',
                    'quantity' => 30,
                    'expiry_date' => '2025-10-15',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Supplement'],
                    'item_name' => 'Iron Supplement Drops',
                    'unit' => 'Bottle (30 ml)',
                    'quantity' => 20,
                    'expiry_date' => '2026-05-20',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Food'],
                    'item_name' => 'Rice Pack',
                    'unit' => 'Sack (25 kg)',
                    'quantity' => 10,
                    'expiry_date' => '2026-01-01',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Equipment'],
                    'item_name' => 'Weighing Scale',
                    'unit' => 'Piece',
                    'quantity' => 5,
                    'expiry_date' => null,
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Medical Supply'],
                    'item_name' => 'Syringe',
                    'unit' => 'Box (100 pcs)',
                    'quantity' => 15,
                    'expiry_date' => '2027-03-15',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Personal Care'],
                    'item_name' => 'Soap Bar',
                    'unit' => 'Piece',
                    'quantity' => 100,
                    'expiry_date' => '2026-08-01',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Sanitation'],
                    'item_name' => 'Alcohol',
                    'unit' => 'Bottle (500 ml)',
                    'quantity' => 40,
                    'expiry_date' => '2026-09-30',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Infant Formula'],
                    'item_name' => 'Infant Milk Formula',
                    'unit' => 'Can (400g)',
                    'quantity' => 25,
                    'expiry_date' => '2026-11-15',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['First Aid'],
                    'item_name' => 'Bandage',
                    'unit' => 'Roll',
                    'quantity' => 60,
                    'expiry_date' => '2027-01-01',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Disinfectant'],
                    'item_name' => 'Bleach',
                    'unit' => 'Bottle (1L)',
                    'quantity' => 30,
                    'expiry_date' => '2026-10-10',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Protective Gear'],
                    'item_name' => 'Face Mask',
                    'unit' => 'Box (50 pcs)',
                    'quantity' => 80,
                    'expiry_date' => '2027-02-28',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Diagnostic Tool'],
                    'item_name' => 'Thermometer',
                    'unit' => 'Piece',
                    'quantity' => 12,
                    'expiry_date' => null,
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Nutritional Product'],
                    'item_name' => 'Protein Powder',
                    'unit' => 'Can (500g)',
                    'quantity' => 18,
                    'expiry_date' => '2026-12-01',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Consumable'],
                    'item_name' => 'Cotton Balls',
                    'unit' => 'Pack (100 pcs)',
                    'quantity' => 35,
                    'expiry_date' => '2026-07-20',
                    'created_at' => now()
                ],
                [
                    'category_id' => $categories['Other'],
                    'item_name' => 'Miscellaneous Item',
                    'unit' => 'Piece',
                    'quantity' => 5,
                    'expiry_date' => null,
                    'created_at' => now()
                ]
            ];

            // Use upsert for production readiness
            DB::table('inventory_items')->upsert($items, ['item_name', 'category_id'], ['unit', 'quantity', 'expiry_date', 'created_at']);
    }
}
