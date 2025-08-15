<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
}
