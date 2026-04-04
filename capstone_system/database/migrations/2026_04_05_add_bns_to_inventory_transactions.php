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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Add bns_id to track which BNS is receiving items during stock out
            $table->foreignId('bns_id')->nullable()->after('patient_id')->constrained('users', 'user_id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['bns_id']);
            $table->dropColumn('bns_id');
        });
    }
};
