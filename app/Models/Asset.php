<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'asset_code',
        'type',
        'hostname',
        'ip_address',
        'brand',
        'model',
        'serial_number',
        'condition',
        'status',
        'notes',
    ];

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
            'hp_root'     => 'HP Root',
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

    public function getJenisIconAttribute(): string
    {
        return match($this->type) {
            'laptop'      => '💻',
            'charger'     => '🔌',
            'mouse'       => '🖱️',
            'lan_adapter' => '🌐',
            'headset'     => '🎧',
            'usb_audio'   => '🎙️',
            'hp_root'     => '📱',
            default       => '📦',
        };
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
