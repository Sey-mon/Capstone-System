<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaysTableSeeder extends Seeder
{
    public function run()
    {
            $barangays = [
                'Bagong Silang',
                'Calendola',
                'Chrysanthemum',
                'Cuyab',
                'Estrella',
                'Fatima',
                'GSIS',
                'Landayan',
                'Langgam',
                'Laram',
                'Magsaysay',
                'Maharlika',
                'Narra',
                'Nueva',
                'Pacita 1',
                'Pacita 2',
                'Poblacion',
                'Riverside',
                'Rosario',
                'Sampaguita',
                'San Antonio',
                'San Lorenzo Ruiz',
                'San Roque',
                'San Vicente',
                'Santo Niño',
                'United Bayanihan',
                'United Better Living.'
            ];

            $data = array_map(fn($name) => ['barangay_name' => $name], $barangays);

            // Use upsert to avoid duplicates in production
            DB::table('barangays')->upsert($data, ['barangay_name'], ['barangay_name']);
    }
}
