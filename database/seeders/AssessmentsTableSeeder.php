<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentsTableSeeder extends Seeder
{
    public function run()
    {
        $assessments = [];
        // 50 patients, 3 types of assessment: improving, worsening, stable
        $statuses = ['Ongoing', 'Recovered', 'Dropped Out'];
        for ($i = 1; $i <= 15; $i++) {
            $nutritionist_id = [2, 3, 4][($i - 1) % 3];
            $base_weight = round(2.5 + rand(6, 60) * 0.3, 2);
            $base_height = round(45 + rand(6, 60) * 1.5, 1);
            $status = $statuses[$i % 3];
            $notes = $status === 'Ongoing' ? 'Patient is showing improvement in weight and height. Continue current intervention.' : ($status === 'Recovered' ? 'Patient has recovered. Discharge planned.' : 'Patient dropped out of program.');
            $treatment = $status === 'Ongoing' ? 'High-protein diet, regular monitoring' : ($status === 'Recovered' ? 'Continue healthy diet at home' : 'Follow-up if possible');
            $assessments[] = [
                'patient_id' => $i,
                'nutritionist_id' => $nutritionist_id,
                'assessment_date' => '2025-08-10',
                'weight_kg' => $base_weight,
                'height_cm' => $base_height,
                'notes' => $notes,
                'treatment' => $treatment,
                'recovery_status' => $status,
                'created_at' => now()
            ];
        }
        DB::table('assessments')->insert($assessments);
    }
}
