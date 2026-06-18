<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDamage extends Model
{
    protected $fillable = [
        'asset_id',
        'reported_by',
        'damage_date',
        'damage_description',
        'action_taken',
        'replacement_asset_id',
        'notes',
    ];

    protected $casts = [
        'damage_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function replacementAsset()
    {
        return $this->belongsTo(Asset::class, 'replacement_asset_id');
    }

    public static function actionLabels()
    {
        return [
            'pending' => 'Pending Action',
            'repair'  => 'Send to Repair',
            'dispose' => 'Disposed (Scrapped)',
            'replace' => 'Replaced',
        ];
    }

    public function getActionLabelAttribute()
    {
        return self::actionLabels()[$this->action_taken] ?? $this->action_taken;
    }
}
