<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';
    protected $primaryKey = 'kb_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ai_summary',
        'pdf_name',
        'pdf_text',
        'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    /**
     * Get the user who added this knowledge base entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get formatted file size if needed
     */
    public function getFormattedSizeAttribute(): string
    {
        if ($this->pdf_text) {
            $bytes = strlen($this->pdf_text);
            $units = ['B', 'KB', 'MB', 'GB'];
            
            for ($i = 0; $bytes > 1024; $i++) {
                $bytes /= 1024;
            }
            
            return round($bytes, 2) . ' ' . $units[$i];
        }
        
        return '0 B';
    }

    /**
     * Get excerpt from PDF text
     */
    public function getExcerptAttribute(): string
    {
        return $this->pdf_text ? substr(strip_tags($this->pdf_text), 0, 200) . '...' : '';
    }

    /**
     * Scope to search knowledge base entries
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('pdf_name', 'like', "%{$search}%")
              ->orWhere('ai_summary', 'like', "%{$search}%")
              ->orWhere('pdf_text', 'like', "%{$search}%");
        });
    }
}