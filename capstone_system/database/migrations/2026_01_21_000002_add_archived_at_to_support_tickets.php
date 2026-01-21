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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('admin_notes');
            $table->index('archived_at');
        });
        
        // Migrate existing soft-deleted records to archived
        DB::table('support_tickets')
            ->whereNotNull('deleted_at')
            ->update([
                'archived_at' => DB::raw('deleted_at'),
                'deleted_at' => null
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['archived_at']);
            $table->dropColumn('archived_at');
        });
    }
};
