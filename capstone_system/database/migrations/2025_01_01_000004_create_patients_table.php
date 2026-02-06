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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id');
            $table->foreignId('parent_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('nutritionist_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->foreignId('barangay_id')->constrained('barangays', 'barangay_id');
            $table->string('contact_number')->nullable();
            $table->integer('age_months');
            $table->enum('sex', ['Male', 'Female']);
            $table->date('date_of_admission');
            $table->integer('total_household_adults')->default(0);
            $table->integer('total_household_children')->default(0);
            $table->integer('total_household_twins')->default(0);
            $table->boolean('is_4ps_beneficiary')->default(false);
            $table->decimal('weight_kg', 5, 2);
            $table->decimal('height_cm', 5, 2);
            $table->string('weight_for_age', 50)->nullable();
            $table->string('height_for_age', 50)->nullable();
            $table->string('bmi_for_age', 50)->nullable();
            $table->enum('breastfeeding', ['Yes', 'No'])->nullable();
            $table->text('other_medical_problems')->nullable();
            $table->enum('edema', ['Yes', 'No'])->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
