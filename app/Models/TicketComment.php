<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'is_admin',
        'comment',
        'attachment',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'is_admin'     => 'boolean',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    public function getReadStatusAttribute(): string
    {
        if ($this->read_at) {
            return 'read'; // ✓✓ Sky Blue (Read)
        }
        if ($this->delivered_at) {
            return 'delivered'; // ✓✓ Gray (Delivered)
        }
        return 'sent'; // ✓ Gray (Sent)
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}