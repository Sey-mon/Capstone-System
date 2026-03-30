<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $primaryKey = 'barangay_id';

    protected $fillable = [
        'barangay_name',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the name attribute (alias for barangay_name)
     */
    public function getNameAttribute()
    {
        return $this->barangay_name;
    }

    /**
     * Get the patients that belong to this barangay.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'barangay_id', 'barangay_id');
    }
}
