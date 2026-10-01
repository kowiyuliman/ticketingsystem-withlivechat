<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChatTemplate extends Model
{
    protected $table = 'chat_templates';

    protected $fillable = [
        'title',
        'category',
        'message',
        'is_active',
        'order_index',
        'created_by',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'order_index' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('chat_templates_active');
        });
        static::deleted(function () {
            Cache::forget('chat_templates_active');
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all active chat templates cached for high-performance livechat rendering
     */
    public static function getActiveTemplates()
    {
        return Cache::remember('chat_templates_active', 86400, function () {
            return static::where('is_active', true)
                ->orderBy('order_index', 'asc')
                ->orderBy('id', 'asc')
                ->get(['id', 'title', 'category', 'message', 'order_index']);
        });
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'on_progress' => 'On Progress',
            'pending'     => 'Pending',
            'closed'      => 'Closed',
            default       => 'Umum / General',
        };
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return match ($this->category) {
            'on_progress' => 'badge-primary',
            'pending'     => 'badge-warning text-dark',
            'closed'      => 'badge-success',
            default       => 'badge-secondary',
        };
    }
}

