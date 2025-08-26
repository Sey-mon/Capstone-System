<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->bigIncrements('kb_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->longText('ai_summary')->nullable();
            $table->string('pdf_name', 255)->nullable();
            $table->longText('pdf_text')->nullable();
            $table->timestamp('added_at')->nullable();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
