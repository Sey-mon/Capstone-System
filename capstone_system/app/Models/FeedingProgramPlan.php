<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedingProgramPlan extends Model
{
    use HasFactory;

    protected $table = 'feeding_program_plans';
    protected $primaryKey = 'program_plan_id';
    public $timestamps = false;

    protected $fillable = [
        'target_age_group',
        'total_children',
        'program_duration_days',
        'budget_level',
        'barangay',
        'available_ingredients',
        'plan_details',
        'generated_at',
        'created_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'plan_details' => 'array', // Auto JSON encode/decode
    ];

    /**
     * Get the nutritionist who created this plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
