<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'item_id',
        'user_id',
        'patient_id',
        'transaction_type',
        'quantity',
        'transaction_date',
        'remarks',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    /**
     * Get the inventory item that owns the transaction.
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id');
    }

    /**
     * Get the item that owns the transaction (alias for inventoryItem).
     */
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id');
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the patient that owns the transaction.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }
}
