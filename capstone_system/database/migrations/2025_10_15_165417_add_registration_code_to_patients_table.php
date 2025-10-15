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
        Schema::table('patients', function (Blueprint $table) {
            // Unique registration code for parent linking
            $table->string('registration_code', 10)->unique()->nullable()->after('parent_id');
            
            // QR code path (for storing generated QR code images)
            $table->string('qr_code_path', 255)->nullable()->after('registration_code');
            
            // When the code was generated
            $table->timestamp('code_generated_at')->nullable()->after('qr_code_path');
            
            // Whether the code has been used to link with a parent
            $table->boolean('code_used')->default(false)->after('code_generated_at');
            
            // When the code expires
            $table->timestamp('code_expires_at')->nullable()->after('code_used');
            
            // Who generated the code (nutritionist user_id)
            $table->unsignedBigInteger('created_by_nutritionist_id')->nullable()->after('code_expires_at');
            
            // Add foreign key for created_by_nutritionist_id
            $table->foreign('created_by_nutritionist_id')->references('user_id')->on('users')->onDelete('set null');
            
            // Add index for faster lookups
            $table->index('registration_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['created_by_nutritionist_id']);
            
            // Drop the columns
            $table->dropColumn([
                'registration_code',
                'qr_code_path',
                'code_generated_at', 
                'code_used',
                'code_expires_at',
                'created_by_nutritionist_id'
            ]);
        });
    }
};
