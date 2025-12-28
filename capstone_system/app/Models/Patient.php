<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Patient extends Model
{
    use HasFactory;

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'parent_id',
        'nutritionist_id',
        'first_name',
        'middle_name',
        'last_name',
        'barangay_id',
        'contact_number',
        'age_months',
        'birthdate',
        'sex',
        'date_of_admission',
        'total_household_adults',
        'total_household_children',
        'total_household_twins',
        'is_4ps_beneficiary',
        'weight_kg',
        'height_cm',
        'weight_for_age',
        'height_for_age',
        'bmi_for_age',
        'breastfeeding',
        'other_medical_problems',
        'edema',
        'custom_patient_id',
    ];

    protected $casts = [
        'date_of_admission' => 'date',
        'birthdate' => 'date',
        'is_4ps_beneficiary' => 'boolean',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
    ];

    /**
     * Boot the model and add event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->custom_patient_id)) {
                try {
                    $patient->custom_patient_id = self::generatePatientId($patient);
                } catch (\Exception $e) {
                    Log::error('Patient ID generation failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'patient_data' => $patient->toArray()
                    ]);
                    
                    throw new \RuntimeException(
                        'Failed to generate patient ID. Please try again.',
                        0,
                        $e
                    );
                }
            }
        });
    }

    /**
     * Generate a unique custom patient ID with thread-safety.
     * Format: YYYY-SP-####-CC
     * 
     * @param Patient $patient
     * @return string
     * @throws \RuntimeException
     */
    private static function generatePatientId($patient): string
    {
        return DB::transaction(function () use ($patient) {
            $programStartYear = config('patient.program_start_year', 2025);
            $prefix = config('patient.id_format.prefix', 'SP');
            $sequentialDigits = config('patient.id_format.sequential_digits', 4);
            $cohortDigits = config('patient.id_format.cohort_digits', 2);
            $currentYear = now()->year;

            // Get the last patient for this year with row locking to prevent race conditions
            $lastPatient = self::whereYear('created_at', $currentYear)
                ->lockForUpdate()
                ->orderBy('patient_id', 'desc')
                ->first();

            // Calculate next sequential number
            if ($lastPatient && $lastPatient->custom_patient_id) {
                // Extract sequence from existing ID (format: YYYY-SP-####-CC)
                $parts = explode('-', $lastPatient->custom_patient_id);
                $sequence = isset($parts[2]) ? intval($parts[2]) + 1 : 1;
            } else {
                $sequence = 1;
            }

            // Calculate cohort year (00 = pre-program, 01 = Year 1, etc.)
            $cohort = max(0, $currentYear - $programStartYear);

            // Generate the custom patient ID
            $customPatientId = sprintf(
                '%d-%s-%0' . $sequentialDigits . 'd-%0' . $cohortDigits . 'd',
                $currentYear,
                $prefix,
                $sequence,
                $cohort
            );

            return $customPatientId;
        });
    }

    /**
     * Get the parent (user) that owns the patient.
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'user_id');
    }

    /**
     * Get the nutritionist (user) that owns the patient.
     */
    public function nutritionist()
    {
        return $this->belongsTo(User::class, 'nutritionist_id', 'user_id');
    }

    /**
     * Get the barangay that owns the patient.
     */
    public function barangay()
    {
        return $this->belongsTo(Barangay::class, 'barangay_id', 'barangay_id');
    }

    /**
     * Get the assessments for the patient.
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the inventory transactions for the patient.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the patient's age in months.
     */
    public function getAgeInMonths()
    {
        // If age_months is directly stored, return it
        if ($this->age_months) {
            return $this->age_months;
        }

        // If we have date_of_birth, calculate from that
        if ($this->date_of_birth) {
            return now()->diffInMonths($this->date_of_birth);
        }

        // Fallback: calculate from date_of_admission if available
        if ($this->date_of_admission) {
            // Assume the age was recorded at admission, so add months since admission
            return $this->age_months + now()->diffInMonths($this->date_of_admission);
        }

        return $this->age_months ?? 0;
    }

    /**
     * Get the patient's gender (alias for sex field).
     */
    public function getGenderAttribute()
    {
        return $this->sex;
    }
}
