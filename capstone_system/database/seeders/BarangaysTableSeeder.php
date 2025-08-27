<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaysTableSeeder extends Seeder
{
   public function run()
    {
        $coordinates = [
            ['barangay_id' => 1, 'barangay_name' => 'Bagong Silang', 'latitude' => 14.347556, 'longitude' => 121.044250],
            ['barangay_id' => 2, 'barangay_name' => 'Calendola', 'latitude' => 14.343524166902826, 'longitude' => 121.03603615519319],
            ['barangay_id' => 3, 'barangay_name' => 'Chrysanthemum', 'latitude' => 14.340617, 'longitude' => 121.045081],
            ['barangay_id' => 4, 'barangay_name' => 'Cuyab', 'latitude' => 14.373410, 'longitude' => 121.057560],
            ['barangay_id' => 5, 'barangay_name' => 'Estrella', 'latitude' => 14.335227, 'longitude' => 121.019513],
            ['barangay_id' => 6, 'barangay_name' => 'Fatima', 'latitude' => 14.354691, 'longitude' => 121.055557],
            ['barangay_id' => 7, 'barangay_name' => 'GSIS', 'latitude' => 14.343875, 'longitude' => 121.019329],
            ['barangay_id' => 8, 'barangay_name' => 'Landayan', 'latitude' => 14.353620, 'longitude' => 121.069585],
            ['barangay_id' => 9, 'barangay_name' => 'Langgam', 'latitude' => 14.329058, 'longitude' => 121.017790],
            ['barangay_id' => 10, 'barangay_name' => 'Laram', 'latitude' => 14.330218, 'longitude' => 121.022778],
            ['barangay_id' => 11, 'barangay_name' => 'Magsaysay', 'latitude' => 14.337507, 'longitude' => 121.033301],
            ['barangay_id' => 12, 'barangay_name' => 'Maharlika', 'latitude' => 14.346550, 'longitude' => 121.045715],
            ['barangay_id' => 13, 'barangay_name' => 'Narra', 'latitude' => 14.331569, 'longitude' => 121.026211],
            ['barangay_id' => 14, 'barangay_name' => 'Nueva', 'latitude' => 14.358588, 'longitude' => 121.057668],
            ['barangay_id' => 15, 'barangay_name' => 'Pacita 1', 'latitude' => 14.345527, 'longitude' => 121.056263],
            ['barangay_id' => 16, 'barangay_name' => 'Pacita 2', 'latitude' => 14.350171, 'longitude' => 121.048225],
            ['barangay_id' => 17, 'barangay_name' => 'Población', 'latitude' => 14.364254, 'longitude' => 121.054303],
            ['barangay_id' => 18, 'barangay_name' => 'Riverside', 'latitude' => 14.342663, 'longitude' => 121.037641],
            ['barangay_id' => 19, 'barangay_name' => 'Rosario', 'latitude' => 14.336618, 'longitude' => 121.047636],
            ['barangay_id' => 20, 'barangay_name' => 'Sampaguita', 'latitude' => 14.344754, 'longitude' => 121.035544],
            ['barangay_id' => 21, 'barangay_name' => 'San Antonio', 'latitude' => 14.364732, 'longitude' => 121.049924],
            ['barangay_id' => 22, 'barangay_name' => 'San Lorenzo Ruiz', 'latitude' => 14.350858, 'longitude' => 121.051375],
            ['barangay_id' => 23, 'barangay_name' => 'San Roque', 'latitude' => 14.367979, 'longitude' => 121.062716],
            ['barangay_id' => 24, 'barangay_name' => 'San Vicente', 'latitude' => 14.357844, 'longitude' => 121.047905],
            ['barangay_id' => 25, 'barangay_name' => 'Santo Niño', 'latitude' => 14.364913, 'longitude' => 121.055873],
            ['barangay_id' => 26, 'barangay_name' => 'United Bayanihan', 'latitude' => 14.334103, 'longitude' => 121.029985],
            ['barangay_id' => 27, 'barangay_name' => 'United Better Living', 'latitude' => 14.337613, 'longitude' => 121.024132],
        ];

    // Delete all rows before seeding to avoid duplicates and set explicit IDs
    DB::table('barangays')->delete();
        foreach ($coordinates as $b) {
            DB::table('barangays')->insert($b);
        }
    }
}
