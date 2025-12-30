<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a unique constraint to prevent multiple assessments
     * for the same patient on the same date.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Add unique index on patient_id and assessment_date combination
            // This prevents creating duplicate assessments for the same patient on the same day
            $table->unique(['patient_id', 'assessment_date'], 'unique_patient_assessment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('unique_patient_assessment_date');
        });
    }
};
