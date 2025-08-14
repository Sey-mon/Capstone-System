<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaysTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('barangays')->insert([
            ['barangay_name' => 'San Isidro'],
            ['barangay_name' => 'Poblacion'],
            ['barangay_name' => 'Mabini'],
            ['barangay_name' => 'Bagong Silang'],
            ['barangay_name' => 'San Vicente']
        ]);
    }
}
