<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'first_name',
        'middle_name',
        'last_name',
        'birth_date',
        'sex',
        'email',
        'password',
        'contact_number',
        'address',
        'is_active',
        // Nutritionist specific fields
        'license_number',
        'years_experience',
        'qualifications',
        'professional_experience',
        'professional_id_path',
        'verification_status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'birth_date' => 'date',
            'years_experience' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the patients assigned to this user as parent.
     */
    public function patientsAsParent()
    {
        return $this->hasMany(Patient::class, 'parent_id', 'user_id');
    }

    /**
     * Get the patients assigned to this user as nutritionist.
     */
    public function patientsAsNutritionist()
    {
        return $this->hasMany(Patient::class, 'nutritionist_id', 'user_id');
    }

    /**
     * Get the assessments performed by this user.
     */
    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'nutritionist_id', 'user_id');
    }

    /**
     * Get the inventory transactions performed by this user.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'user_id');
    }

    /**
     * Get the user who verified this nutritionist.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    /**
     * Get the full name of the user.
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
     * Check if user is a nutritionist.
     */
    public function isNutritionist(): bool
    {
        return $this->role && $this->role->role_name === 'Nutritionist';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->role_name === 'Admin';
    }

    /**
     * Check if user is a parent.
     */
    public function isParent(): bool
    {
        return $this->role && $this->role->role_name === 'Parent';
    }

    /**
     * Check if nutritionist verification is pending.
     */
    public function isVerificationPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if nutritionist is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if nutritionist verification was rejected.
     */
    public function isVerificationRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Check if account is pending approval.
     */
    public function isAccountPending(): bool
    {
        return $this->account_status === 'pending';
    }

    /**
     * Check if account is active.
     */
    public function isAccountActive(): bool
    {
        return $this->account_status === 'active';
    }

    /**
     * Approve nutritionist verification.
     */
    public function approveVerification(User $verifier = null): bool
    {
        $this->verification_status = 'verified';
        $this->account_status = 'active';
        $this->verified_at = now();
        
        if ($verifier) {
            $this->verified_by = $verifier->user_id;
        }
        
        return $this->save();
    }

    /**
     * Reject nutritionist verification.
     */
    public function rejectVerification(string $reason, User $verifier = null): bool
    {
        $this->verification_status = 'rejected';
        $this->account_status = 'rejected';
        $this->rejection_reason = $reason;
        $this->verified_at = now();
        
        if ($verifier) {
            $this->verified_by = $verifier->user_id;
        }
        
        return $this->save();
    }

    /**
     * Get the specialization display name.
     */
    public function getSpecializationDisplayAttribute(): string
    {
        return 'Not specified';
    }

    /**
     * Get the verification status badge class for UI.
     */
    public function getVerificationBadgeClassAttribute(): string
    {
        return match($this->verification_status) {
            'pending' => 'badge-warning',
            'verified' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get the account status badge class for UI.
     */
    public function getAccountStatusBadgeClassAttribute(): string
    {
        return match($this->account_status) {
            'pending' => 'badge-warning',
            'active' => 'badge-success',
            'suspended' => 'badge-warning',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
