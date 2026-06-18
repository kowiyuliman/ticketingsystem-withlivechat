<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRepair extends Model
{
    protected $fillable = [
        'asset_id',
        'reported_by',
        'issue_description',
        'repair_date',
        'completed_date',
        'repair_cost',
        'repair_vendor',
        'status',
        'resolution',
    ];

    protected $casts = [
        'repair_date'    => 'date',
        'completed_date' => 'date',
        'repair_cost'    => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending'     => 'warning',
            'in_progress' => 'info',
            'completed'   => 'success',
            'cancelled'   => 'danger',
            default       => 'secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending'     => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
            default       => $this->status,
        };
    }
}
