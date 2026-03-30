<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientTableSeeder extends Seeder
{
    public function run()
    {
        // Parent user_ids from UsersTableSeeder
        $parent_ids = [5, 6, 7, 8, 9]; // Valid parent user_ids
        $nutritionist_ids = [2, 3, 4]; // Valid nutritionist user_ids
        $barangay_ids = range(1, 27); // 27 barangays
        $sex_options = ['Male', 'Female'];
        $breastfeeding_options = ['Yes', 'No'];
        $medical_problems = [null, 'Anemia', 'Asthma', 'Diarrhea', 'None'];

        // Use Patient::create() to trigger model events and auto-generate custom_patient_id
        for ($i = 1; $i <= 15; $i++) {
            $age_months = rand(6, 60);
            $sex = $sex_options[array_rand($sex_options)];
            // Reasonable weight/height for age
            $weight_kg = round(2.5 + $age_months * 0.3 + rand(-10, 10) * 0.05, 2);
            $height_cm = round(45 + $age_months * 1.5 + rand(-10, 10) * 0.5, 1);
            
            Patient::create([
                'parent_id' => $parent_ids[array_rand($parent_ids)],
                'nutritionist_id' => $nutritionist_ids[array_rand($nutritionist_ids)],
                'first_name' => 'Patient' . $i,
                'middle_name' => chr(65 + ($i % 26)) . '.',
                'last_name' => 'Testcase',
                'barangay_id' => $barangay_ids[array_rand($barangay_ids)],
                'contact_number' => '09' . rand(100000000, 999999999),
                'age_months' => $age_months,
                'sex' => $sex,
                'date_of_admission' => date('Y-m-d', strtotime("2025-08-01 +$i days")),
                'total_household_adults' => rand(1, 4),
                'total_household_children' => rand(1, 6),
                'total_household_twins' => rand(0, 1),
                'is_4ps_beneficiary' => (bool)rand(0, 1),
                'weight_kg' => $weight_kg,
                'height_cm' => $height_cm,
                'breastfeeding' => $breastfeeding_options[array_rand($breastfeeding_options)],
                'other_medical_problems' => $medical_problems[array_rand($medical_problems)],
                'edema' => rand(0, 1) ? 'Yes' : 'No',
            ]);
        }
    }
}
