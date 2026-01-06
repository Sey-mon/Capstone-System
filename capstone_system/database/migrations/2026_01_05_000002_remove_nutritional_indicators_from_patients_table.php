<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove nutritional indicators from patients table.
     * These indicators are now stored only in the assessments table.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['weight_for_age', 'height_for_age', 'bmi_for_age']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('weight_for_age')->nullable()->after('height_cm');
            $table->string('height_for_age')->nullable()->after('weight_for_age');
            $table->string('bmi_for_age')->nullable()->after('height_for_age');
        });
    }
};
