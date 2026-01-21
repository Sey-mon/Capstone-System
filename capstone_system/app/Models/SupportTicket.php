<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'ticket_number',
        'reporter_email',
        'category',
        'subject',
        'description',
        'other_specify',
        'status',
        'priority',
        'ip_address',
        'read_at',
        'admin_notes',
        'archived_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    /**
     * Boot method for auto-generating ticket numbers and setting priority
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Auto-generate ticket number
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
            
            // Auto-set priority based on category
            if (!$ticket->priority) {
                $urgentCategories = ['authentication', 'account_access', 'technical_bug'];
                $ticket->priority = in_array($ticket->category, $urgentCategories) ? 'urgent' : 'normal';
            }
        });
    }

    /**
     * Generate unique ticket number in format TKT-YYYY-NNNN
     */
    private static function generateTicketNumber()
    {
        $year = date('Y');
        // Include archived tickets in the count to never reuse ticket numbers
        $lastTicket = self::whereYear('created_at', $year)
            ->orderBy('ticket_id', 'desc')
            ->first();
        
        $sequence = $lastTicket ? (int)substr($lastTicket->ticket_number, -4) + 1 : 1;
        
        return 'TKT-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope for unread tickets
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Scope for urgent tickets
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for unresolved tickets
     */
    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['unread', 'read']);
    }
    /**
     * Scope for active (non-archived) tickets
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /**
     * Scope for archived tickets only
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
    /**
     * Get formatted category name
     */
    public function getCategoryNameAttribute()
    {
        return match($this->category) {
            'authentication' => 'Login/Authentication Issues',
            'account_access' => 'Account Access Problems',
            'patient_management' => 'Patient Management',
            'assessment_issues' => 'Assessment/Screening',
            'meal_planning' => 'Meal Planning',
            'inventory_system' => 'Inventory System',
            'reports_analytics' => 'Reports & Analytics',
            'ai_service' => 'AI Assistant',
            'technical_bug' => 'Technical Bug/Error',
            'data_error' => 'Data Error',
            'feature_request' => 'Feature Request',
            'performance_issue' => 'Performance Issue',
            'mobile_display' => 'Mobile Display',
            'other' => 'Other',
            default => $this->category,
        };
    }
}
