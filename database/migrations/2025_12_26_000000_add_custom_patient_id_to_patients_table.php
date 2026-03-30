<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add custom_patient_id column
        Schema::table('patients', function (Blueprint $table) {
            $table->string('custom_patient_id', 20)->nullable()->after('patient_id');
        });

        // Add unique constraint
        Schema::table('patients', function (Blueprint $table) {
            $table->unique('custom_patient_id');
        });

        // Add compound index for performance optimization
        Schema::table('patients', function (Blueprint $table) {
            $table->index(['created_at', 'patient_id'], 'idx_patients_year_lookup');
        });

        // Backfill existing patients with custom IDs
        $this->backfillPatientIds();

        // Make custom_patient_id non-nullable after backfill
        Schema::table('patients', function (Blueprint $table) {
            $table->string('custom_patient_id', 20)->nullable(false)->change();
        });
    }

    /**
     * Backfill custom_patient_id for existing patients
     */
    private function backfillPatientIds(): void
    {
        DB::transaction(function () {
            $programStartYear = config('patient.program_start_year', 2025);
            $prefix = config('patient.id_format.prefix', 'SP');
            $sequentialDigits = config('patient.id_format.sequential_digits', 4);
            $cohortDigits = config('patient.id_format.cohort_digits', 2);

            // Get all patients grouped by year
            $patientsByYear = DB::table('patients')
                ->whereNull('custom_patient_id')
                ->orderBy('created_at')
                ->orderBy('patient_id')
                ->get()
                ->groupBy(function ($patient) {
                    return date('Y', strtotime($patient->created_at));
                });

            foreach ($patientsByYear as $year => $patients) {
                $sequence = 1;
                
                foreach ($patients as $patient) {
                    // Calculate cohort year
                    $cohort = max(0, (int)$year - $programStartYear);
                    
                    // Generate custom patient ID
                    $customPatientId = sprintf(
                        '%d-%s-%0' . $sequentialDigits . 'd-%0' . $cohortDigits . 'd',
                        $year,
                        $prefix,
                        $sequence,
                        $cohort
                    );

                    // Update patient
                    DB::table('patients')
                        ->where('patient_id', $patient->patient_id)
                        ->update(['custom_patient_id' => $customPatientId]);

                    $sequence++;
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_year_lookup');
            $table->dropUnique(['custom_patient_id']);
            $table->dropColumn('custom_patient_id');
        });
    }
};
