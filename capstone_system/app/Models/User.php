<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Notifications\CustomVerifyEmail;
use App\Services\DataEncryptionService;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait {
        MustVerifyEmailTrait::sendEmailVerificationNotification as originalSendEmailVerificationNotification;
    }

    protected $primaryKey = 'user_id';

    /**
     * Fields that should be encrypted when storing in database
     * 
     * @var array
     */
    protected $encrypted = [
        'email',
        'contact_number', 
        'address'
    ];

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
        'suffix',
        'birth_date',
        'sex',
        'email',
        'email_verified_at',
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

    // ========== ENCRYPTION METHODS ==========

    /**
     * Get the encryption service instance
     */
    private function getEncryptionService()
    {
        return app(DataEncryptionService::class);
    }

    /**
     * Override getAttribute to decrypt encrypted fields when accessing
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // If this field should be encrypted and has a value, decrypt it
        if (in_array($key, $this->encrypted) && !empty($value)) {
            $decrypted = $this->getEncryptionService()->decryptUserData($value);
            return $decrypted !== null ? $decrypted : $value;
        }
        
        return $value;
    }

    /**
     * Override setAttribute to encrypt fields before storing
     */
    public function setAttribute($key, $value)
    {
        // If this field should be encrypted and has a value, encrypt it
        if (in_array($key, $this->encrypted) && !empty($value)) {
            // Only encrypt if not already encrypted
            if (!$this->getEncryptionService()->isEncrypted($value)) {
                $encrypted = $this->getEncryptionService()->encryptUserData($value);
                $value = $encrypted !== null ? $encrypted : $value;
            }
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Find user by encrypted email
     */
    public static function findByEmail($email)
    {
        $encryptionService = app(DataEncryptionService::class);
        
        // First try to find by plaintext email (for backward compatibility)
        $user = static::where('email', $email)->whereNull('deleted_at')->first();
        
        if (!$user) {
            // If not found, search through encrypted emails
            $allUsers = static::whereNotNull('email')->whereNull('deleted_at')->get();
            
            foreach ($allUsers as $potentialUser) {
                $decryptedEmail = $encryptionService->decryptUserData($potentialUser->getRawOriginal('email'));
                if ($decryptedEmail && strtolower($decryptedEmail) === strtolower($email)) {
                    return $potentialUser;
                }
            }
            return null; // Not found
        }
        
        return $user;
    }

    /**
     * Get the raw (encrypted) value of an attribute
     */
    public function getRawEncrypted($key)
    {
        return parent::getAttribute($key);
    }

    /**
     * Check if email already exists (handles both encrypted and plain)
     */
    public static function emailExists($email)
    {
        return static::findByEmail($email) !== null;
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
    public function approveVerification(?User $verifier = null): bool
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
    public function rejectVerification(string $reason, ?User $verifier = null): bool
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

    /**
     * Send the email verification notification using custom template.
     * Override the default Laravel method to use our custom notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    /**
     * Get the email address that should be used for verification.
     * This method handles encrypted emails properly.
     */
    public function getEmailForVerification()
    {
        // Get the decrypted email for verification
        $encryptionService = $this->getEncryptionService();
        
        if ($encryptionService->isEncrypted($this->email)) {
            return $encryptionService->decryptUserData($this->email);
        }
        
        return $this->email;
    }


}
