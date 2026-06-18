<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    protected $fillable = [
        'asset_assignment_id',
        'user_id',
        'loan_date',
        'return_date',
        'guarantee_type',
        'guarantee_number',
        'status',
        'notes',
        'approved_by',
    ];

    protected $casts = [
        'loan_date'   => 'date',
        'return_date'  => 'date',
    ];

    public function assignment()
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function getGuaranteeLabelAttribute()
    {
        return strtoupper($this->guarantee_type);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'borrowed' => 'warning',
            'returned' => 'success',
            default    => 'secondary',
        };
    }
}
