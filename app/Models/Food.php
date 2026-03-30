<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';
    protected $primaryKey = 'food_id';
    public $timestamps = false;

    protected $fillable = [
        'food_name_and_description',
        'alternate_common_names',
        'energy_kcal',
        'nutrition_tags',
    ];

    protected $casts = [
        'energy_kcal' => 'float',
    ];

    /**
     * Get nutrition tags as an array
     */
    public function getNutritionTagsArrayAttribute()
    {
        if (empty($this->nutrition_tags)) {
            return [];
        }
        return array_filter(array_map('trim', explode(',', $this->nutrition_tags)));
    }

    /**
     * Scope to search foods
     */
    public function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            return $query->where(function($q) use ($search) {
                $q->where('food_name_and_description', 'like', "%{$search}%")
                  ->orWhere('alternate_common_names', 'like', "%{$search}%")
                  ->orWhere('nutrition_tags', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope to filter by nutrition tag
     */
    public function scopeWithTag($query, $tag)
    {
        if (!empty($tag)) {
            return $query->where('nutrition_tags', 'like', "%{$tag}%");
        }
        return $query;
    }
}
