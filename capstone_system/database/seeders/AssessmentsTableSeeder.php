<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('assessments')->insert([
            [
                'patient_id' => 1,
                'nutritionist_id' => 2,
                'assessment_date' => '2025-08-10',
                'weight_kg' => 8.80,
                'height_cm' => 75.5,
                'notes' => 'Slight improvement in weight. Continue feeding program.',
                'treatment' => 'Vitamin A supplement, high-protein diet',
                'recovery_status' => 'Ongoing',
                'created_at' => now()
            ],
            [
                'patient_id' => 2,
                'nutritionist_id' => 2,
                'assessment_date' => '2025-08-12',
                'weight_kg' => 10.50,
                'height_cm' => 82.2,
                'notes' => 'Maintaining normal growth.',
                'treatment' => 'Continue current diet',
                'recovery_status' => 'Ongoing',
                'created_at' => now()
            ]
        ]);
    }
}
