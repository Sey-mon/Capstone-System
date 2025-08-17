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
        Schema::table('users', function (Blueprint $table) {
            // Professional information for nutritionists
            $table->string('license_number')->nullable()->unique();
            $table->integer('years_experience')->nullable()->default(0);
            $table->text('qualifications')->nullable();
            $table->text('professional_experience')->nullable();
            
            // Professional ID verification
            $table->string('professional_id_path')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            
            // Account status for pending applications
            $table->enum('account_status', ['pending', 'active', 'suspended', 'rejected'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'license_number',
                'years_experience',
                'qualifications',
                'professional_experience',
                'professional_id_path',
                'verification_status',
                'rejection_reason',
                'verified_at',
                'verified_by',
                'account_status'
            ]);
        });
    }
};
