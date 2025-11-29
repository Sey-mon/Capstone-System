<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feeding_program_plans', function (Blueprint $table) {
            $table->bigIncrements('program_plan_id');
            $table->string('target_age_group', 50); // 'all', '0-12months', etc.
            $table->integer('total_children')->nullable();
            $table->integer('program_duration_days');
            $table->string('budget_level', 20); // 'low', 'moderate', 'high'
            $table->string('barangay', 100)->nullable();
            $table->text('available_ingredients')->nullable();
            $table->longText('plan_details'); // JSON meal plan data
            $table->timestamp('generated_at');
            $table->unsignedBigInteger('created_by')->nullable(); // nutritionist user_id
            
            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeding_program_plans');
    }
};
