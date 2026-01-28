<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;
use App\Models\Barangay;
use Carbon\Carbon;

class OldPatientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test patients who are 5 years old and above for testing archive functionality
     */
    public function run(): void
    {
        $this->command->info('Creating patients aged 5 years and above...');

        // Get a nutritionist and barangay
        $nutritionist = User::whereHas('role', function($q) {
            $q->where('role_name', 'Nutritionist');
        })->first();

        $barangay = Barangay::first();

        if (!$nutritionist || !$barangay) {
            $this->command->error('Please ensure you have at least one nutritionist and one barangay in the database.');
            return;
        }

        // Create 10 patients aged between 5-7 years old
        $patients = [
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'sex' => 'Female', 'age_years' => 5],
            ['first_name' => 'Juan', 'last_name' => 'Cruz', 'sex' => 'Male', 'age_years' => 5],
            ['first_name' => 'Anna', 'last_name' => 'Reyes', 'sex' => 'Female', 'age_years' => 6],
            ['first_name' => 'Pedro', 'last_name' => 'Garcia', 'sex' => 'Male', 'age_years' => 6],
            ['first_name' => 'Sofia', 'last_name' => 'Dela Cruz', 'sex' => 'Female', 'age_years' => 7],
            ['first_name' => 'Miguel', 'last_name' => 'Ramos', 'sex' => 'Male', 'age_years' => 5],
            ['first_name' => 'Isabella', 'last_name' => 'Torres', 'sex' => 'Female', 'age_years' => 6],
            ['first_name' => 'Carlos', 'last_name' => 'Mendoza', 'sex' => 'Male', 'age_years' => 7],
            ['first_name' => 'Gabriela', 'last_name' => 'Flores', 'sex' => 'Female', 'age_years' => 5],
            ['first_name' => 'Diego', 'last_name' => 'Pascual', 'sex' => 'Male', 'age_years' => 6],
        ];

        foreach ($patients as $patientData) {
            $ageInYears = $patientData['age_years'];
            $ageInMonths = $ageInYears * 12;
            
            // Calculate birthdate (5-7 years ago from today)
            $birthdate = Carbon::now()->subYears($ageInYears)->subDays(rand(0, 364));
            
            // Date of admission (1 year ago)
            $dateOfAdmission = Carbon::now()->subYear();

            Patient::create([
                'nutritionist_id' => $nutritionist->user_id,
                'first_name' => $patientData['first_name'],
                'middle_name' => 'Test',
                'last_name' => $patientData['last_name'],
                'barangay_id' => $barangay->barangay_id,
                'contact_number' => '09' . rand(100000000, 999999999),
                'age_months' => $ageInMonths,
                'birthdate' => $birthdate,
                'sex' => $patientData['sex'],
                'date_of_admission' => $dateOfAdmission,
                'total_household_adults' => rand(1, 3),
                'total_household_children' => rand(1, 4),
                'total_household_twins' => 0,
                'is_4ps_beneficiary' => (bool)rand(0, 1),
                'weight_kg' => rand(15, 25),
                'height_cm' => rand(100, 120),
                'breastfeeding' => 'No',
                'allergies' => null,
                'religion' => 'Roman Catholic',
                'other_medical_problems' => null,
                'edema' => 'No',
            ]);

            $this->command->info("✓ Created: {$patientData['first_name']} {$patientData['last_name']} ({$ageInYears} years old)");
        }

        $this->command->info('✅ Successfully created ' . count($patients) . ' patients aged 5+ years!');
        $this->command->warn('💡 Run: php artisan patients:archive-eligible --dry-run to see them');
    }
}
