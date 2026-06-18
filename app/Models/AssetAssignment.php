<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'asset_id',
        'assigned_at',
        'returned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function loans()
    {
        return $this->hasMany(AssetLoan::class);
    }

    // Scope: hanya assignment aktif (belum dikembalikan)
    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }
}
