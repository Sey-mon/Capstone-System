<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'date_of_admission' => 'date',
        'is_4ps_beneficiary' => 'boolean',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
    ];

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
