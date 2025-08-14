<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->foreignId('item_id')->constrained('inventory_items', 'item_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('patient_id')->nullable()->constrained('patients', 'patient_id')->nullOnDelete();
            $table->enum('transaction_type', ['In', 'Out']);
            $table->integer('quantity');
            $table->timestamp('transaction_date')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
