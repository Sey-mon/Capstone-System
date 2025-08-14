<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryTransactionController extends Controller
{
    public function index()
    {
        $transactions = InventoryTransaction::with(['inventoryItem', 'user', 'patient'])
            ->latest('transaction_date')
            ->paginate(20);
        return view('admin.inventory-transactions', compact('transactions'));
    }
}
