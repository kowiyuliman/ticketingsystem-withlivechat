<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code',
        'user_id',
        'nama',
        'nomor_meja',
        'nomor_ruangan',
        'ip_address',
        'nomor_laptop',
        'kategori',
        'deskripsi',
        'screenshot',
        'status',
        'status_reason',
        'assigned_to',
        'started_at',
        'created_by',
        'resolved_at',
        'no_whatsapp',
        'merged_to',
        'merge_reason'
    ];

    public function getReasonTextAttribute()
    {
        if (!empty($this->status_reason)) {
            return $this->status_reason;
        }

        if ($this->relationLoaded('histories') && ($this->status === 'pending' || $this->status === 'cancelled')) {
            $history = $this->histories
                ->whereIn('status', ['pending', 'cancelled'])
                ->sortByDesc('created_at')
                ->first();

            if ($history && !empty($history->keterangan)) {
                if (\Illuminate\Support\Str::contains($history->keterangan, 'Keterangan: ')) {
                    return \Illuminate\Support\Str::after($history->keterangan, 'Keterangan: ');
                }
                return $history->keterangan;
            }
        }

        return null;
    }

    protected $casts = [
    'started_at' => 'datetime',
    'resolved_at' => 'datetime',
    ];

    protected $dates = [
    'started_at',
    'resolved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function histories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class,'assigned_to');
    }

    public function getSlaAttribute()
    {
        if ($this->started_at && $this->resolved_at) {

        $minutes = $this->started_at->diffInMinutes($this->resolved_at);

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return $hours.' jam '.$mins.' menit';
        
    }

    // 🔥 kalau belum selesai → hitung realtime
    if ($this->started_at && !$this->resolved_at) {

        $minutes = $this->started_at->diffInMinutes(now());

        return $minutes . ' menit (berjalan)';
    }

    return '-';

    if ($this->started_at) {
        $hours = $this->started_at->diffInHours(now());
        return 'On Progress (' . $hours . ' jam)';
    }

    return 'Belum mulai';
    }

    public function getDurationAttribute()
    {
        if($this->started_at && $this->resolved_at){
            return $this->started_at->diffForHumans($this->resolved_at, true);
        }

        if($this->started_at){
            return 'Sedang berjalan: '.$this->started_at->diffForHumans(now(), true);
        }

        return 'Belum ada aktivitas';
    }

    public function getDurasiMenitAttribute()
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->resolved_at ?? now();

        $totalMinutes = $this->started_at->diffInMinutes($end);

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return $hours . ' jam ' . $minutes . ' menit';
        } elseif ($hours > 0) {
            return $hours . ' jam';
        } else {
            return $minutes . ' menit';
        }
    }

    public function timelines()
    {
        return $this->hasMany(TicketTimeline::class);
    }

    public function mergedTicket()
    {
        return $this->belongsTo(Ticket::class, 'merged_to');
    }

    public function mergedChildren()
    {
        return $this->hasMany(Ticket::class, 'merged_to');
    }

    public function mergedTickets()
    {
        return $this->hasMany(
            Ticket::class,
            'merged_to'
        );
    }

    /**
     * Generate structured ticket code: MPTB-IT-YYYYMMDD-{KAT}-XXX
     * e.g. MPTB-IT-20260915-HW-001
     * hw -> HW, sw -> SW, ntw -> NTW, oth -> OTH
     * Resets daily sequence per category (3 digits padding).
     */
    public static function generateTicketCode(?string $kategori, ?string $date = null): string
    {
        $katCode = match(strtolower(trim($kategori ?? ''))) {
            'hardware' => 'HW',
            'software' => 'SW',
            'network'  => 'NTW',
            'other'    => 'OTH',
            default    => 'OTH',
        };

        $dateStr = $date ?: date('Ymd');
        $prefix = "MPTB-IT-{$dateStr}-{$katCode}-";

        $latest = static::where('ticket_code', 'LIKE', "{$prefix}%")
            ->orderByDesc('ticket_code')
            ->first();

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->ticket_code, $matches)) {
            $seq = (int)$matches[1] + 1;
        }

        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}