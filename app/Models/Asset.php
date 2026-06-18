<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_code',
        'type',
        'brand',
        'model',
        'serial_number',
        'condition',
        'status',
        'notes',
    ];

    // Auto-generate asset_code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (empty($asset->asset_code)) {
                $lastAsset = static::orderBy('id', 'desc')->first();
                $nextId = $lastAsset ? $lastAsset->id + 1 : 1;
                $asset->asset_code = 'AST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Label tipe asset
    public static function typeLabels()
    {
        return [
            'laptop'      => 'Laptop',
            'charger'     => 'Charger',
            'mouse'       => 'Mouse',
            'lan_adapter' => 'LAN Adapter',
            'headset'     => 'Headset',
            'usb_audio'   => 'USB Audio',
        ];
    }

    // Label status
    public static function statusLabels()
    {
        return [
            'available'  => 'Available',
            'assigned'   => 'Assigned',
            'on_loan'    => 'On Loan',
            'in_repair'  => 'In Repair',
            'damaged'    => 'Damaged',
        ];
    }

    // Brand laptop
    public static function laptopBrands()
    {
        return ['HP', 'Dell', 'Lenovo'];
    }

    // Relasi
    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_at')->latest();
    }

    public function repairs()
    {
        return $this->hasMany(AssetRepair::class);
    }

    public function damages()
    {
        return $this->hasMany(AssetDamage::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper
    public function getTypeLabelAttribute()
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'available' => 'success',
            'assigned'  => 'primary',
            'on_loan'   => 'warning',
            'in_repair' => 'info',
            'damaged'   => 'danger',
            default     => 'secondary',
        };
    }
}
