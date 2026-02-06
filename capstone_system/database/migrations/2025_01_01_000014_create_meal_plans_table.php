<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->bigIncrements('plan_id');
            $table->unsignedBigInteger('patient_id');
            $table->text('plan_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreign('patient_id')
                ->references('patient_id')
                ->on('patients')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
