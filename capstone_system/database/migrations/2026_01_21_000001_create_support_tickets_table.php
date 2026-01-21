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
        Schema::create('support_tickets', function (Blueprint $table) {
            // Primary key & reference number
            $table->id('ticket_id');
            $table->string('ticket_number', 20)->unique();
            
            // Reporter information
            $table->string('reporter_email', 255);
            
            // Ticket content
            $table->enum('category', [
                'authentication',
                'account_access',
                'patient_management',
                'assessment_issues',
                'meal_planning',
                'inventory_system',
                'reports_analytics',
                'ai_service',
                'technical_bug',
                'data_error',
                'feature_request',
                'performance_issue',
                'mobile_display',
                'other'
            ]);
            $table->string('subject', 255);
            $table->text('description');
            $table->string('other_specify', 255)->nullable();
            
            // Status & priority
            $table->enum('status', ['unread', 'read', 'resolved'])->default('unread');
            $table->enum('priority', ['urgent', 'normal'])->default('normal');
            
            // Technical information
            $table->string('ip_address', 45)->nullable();
            
            // Admin tracking
            $table->timestamp('read_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
            $table->index(['status', 'priority'], 'idx_ticket_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
