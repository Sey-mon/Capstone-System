<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->bigIncrements('food_id');
            $table->text('food_name_and_description')->nullable();
            $table->text('alternate_common_names')->nullable();
            $table->float('energy_kcal')->nullable();
            $table->text('nutrition_tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
