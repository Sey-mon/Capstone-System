<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assessment_id';

    protected $fillable = [
        'patient_id',
        'nutritionist_id',
        'assessment_date',
        'weight_kg',
        'height_cm',
        'notes',
        'treatment',
        'recovery_status',
        'completed_at',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'weight_kg' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the patient that owns the assessment.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the nutritionist (user) that owns the assessment.
     */
    public function nutritionist()
    {
        return $this->belongsTo(User::class, 'nutritionist_id', 'user_id');
    }
}
