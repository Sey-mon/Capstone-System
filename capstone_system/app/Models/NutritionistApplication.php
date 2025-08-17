<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NutritionistApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact_number',
        'date_of_birth',
        'gender',
        'license_number',
        'specialization',
        'years_experience',
        'clinic_address',
        'qualifications',
        'experience',
        'professional_id_path',
        'username',
        'password',
        'application_status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'years_experience' => 'integer',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who reviewed this application
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the full name of the applicant
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->first_name;
        
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        
        $name .= ' ' . $this->last_name;
        
        return $name;
    }

    /**
     * Check if application is pending
     */
    public function isPending(): bool
    {
        return $this->application_status === 'pending';
    }

    /**
     * Check if application is approved
     */
    public function isApproved(): bool
    {
        return $this->application_status === 'approved';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }

    /**
     * Approve the application
     */
    public function approve(User $reviewer = null): bool
    {
        $this->application_status = 'approved';
        $this->reviewed_at = now();
        
        if ($reviewer) {
            $this->reviewed_by = $reviewer->id;
        }
        
        return $this->save();
    }

    /**
     * Reject the application
     */
    public function reject(string $reason, User $reviewer = null): bool
    {
        $this->application_status = 'rejected';
        $this->rejection_reason = $reason;
        $this->reviewed_at = now();
        
        if ($reviewer) {
            $this->reviewed_by = $reviewer->id;
        }
        
        return $this->save();
    }

    /**
     * Get the specialization display name
     */
    public function getSpecializationDisplayAttribute(): string
    {
        $specializations = [
            'clinical_nutrition' => 'Clinical Nutrition',
            'sports_nutrition' => 'Sports Nutrition',
            'pediatric_nutrition' => 'Pediatric Nutrition',
            'geriatric_nutrition' => 'Geriatric Nutrition',
            'weight_management' => 'Weight Management',
            'eating_disorders' => 'Eating Disorders',
            'community_nutrition' => 'Community Nutrition',
            'other' => 'Other',
        ];

        return $specializations[$this->specialization] ?? 'Unknown';
    }

    /**
     * Get the status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->application_status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get the status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->application_status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }
}
