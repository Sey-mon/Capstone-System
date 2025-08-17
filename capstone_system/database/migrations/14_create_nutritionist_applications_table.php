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
        Schema::create('nutritionist_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('contact_number');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('license_number')->unique();
            $table->enum('specialization', [
                'clinical_nutrition',
                'sports_nutrition',
                'pediatric_nutrition',
                'geriatric_nutrition',
                'weight_management',
                'eating_disorders',
                'community_nutrition',
                'other'
            ]);
            $table->integer('years_experience')->default(0);
            $table->string('clinic_address')->nullable();
            $table->text('qualifications');
            $table->text('experience');
            $table->string('professional_id_path');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('application_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutritionist_applications');
    }
};
