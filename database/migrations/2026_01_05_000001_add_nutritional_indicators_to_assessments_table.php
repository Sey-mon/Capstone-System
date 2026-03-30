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
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('weight_for_age')->nullable()->after('height_cm');
            $table->string('height_for_age')->nullable()->after('weight_for_age');
            $table->string('bmi_for_age')->nullable()->after('height_for_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['weight_for_age', 'height_for_age', 'bmi_for_age']);
        });
    }
};
