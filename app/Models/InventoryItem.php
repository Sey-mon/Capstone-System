<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'category_id',
        'item_name',
        'unit',
        'quantity',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Get the category that owns the inventory item.
     */
    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id', 'category_id');
    }

    /**
     * Get the inventory transactions for the item.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id', 'item_id');
    }
}
