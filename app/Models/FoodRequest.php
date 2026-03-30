<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'food_name_and_description',
        'alternate_common_names',
        'energy_kcal',
        'nutrition_tags',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'energy_kcal' => 'float',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who requested the food
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by', 'user_id');
    }

    /**
     * Get the admin who reviewed the request
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'user_id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
