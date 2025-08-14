<?php

// Simple test to verify InventoryItem relationship
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryItem;

try {
    $item = InventoryItem::with('inventoryTransactions')->first();
    if ($item) {
        echo "✅ Success! Relationship works correctly.\n";
        echo "Item: {$item->item_name}\n";
        echo "Transactions: {$item->inventoryTransactions->count()}\n";
    } else {
        echo "ℹ️  No inventory items found in database.\n";
    }
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}
