<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('patients')->insert([
            [
                'parent_id' => 3, // Parent user_id from UsersTableSeeder
                'nutritionist_id' => 2, // Nutritionist user_id
                'first_name' => 'Ana',
                'middle_name' => 'G.',
                'last_name' => 'Reyes',
                'barangay_id' => 1, // San Isidro
                'contact_number' => '09332221111',
                'age_months' => 24,
                'sex' => 'Female',
                'date_of_admission' => '2025-08-01',
                'total_household_adults' => 2,
                'total_household_children' => 3,
                'total_household_twins' => 0,
                'is_4ps_beneficiary' => true,
                'weight_kg' => 8.50,
                'height_cm' => 75.0,
                'weight_for_age' => 'Underweight',
                'height_for_age' => 'Stunted',
                'bmi_for_age' => 'Severe Wasting',
                'breastfeeding' => 'Yes',
                'other_medical_problems' => 'Anemia',
                'edema' => 'No',
                'created_at' => now()
            ],
            [
                'parent_id' => 3,
                'nutritionist_id' => 2,
                'first_name' => 'Mark',
                'middle_name' => 'D.',
                'last_name' => 'Santos',
                'barangay_id' => 2, // Poblacion
                'contact_number' => '09334445555',
                'age_months' => 36,
                'sex' => 'Male',
                'date_of_admission' => '2025-08-05',
                'total_household_adults' => 2,
                'total_household_children' => 4,
                'total_household_twins' => 1,
                'is_4ps_beneficiary' => false,
                'weight_kg' => 10.20,
                'height_cm' => 82.0,
                'weight_for_age' => 'Normal',
                'height_for_age' => 'Normal',
                'bmi_for_age' => 'Normal',
                'breastfeeding' => 'No',
                'other_medical_problems' => null,
                'edema' => 'No',
                'created_at' => now()
            ]
        ]);
    }
}
