<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'jenis',
        'merk',
        'sn',
        'tanggal_masuk',
        'kepemilikan',
        'pengguna',
        'kontak',
        'department',
        'tanggal_signin',
        'team_leader',
        'lokasi',
        'hak_bawa_pulang',
        'kondisi',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_signin' => 'date',
        'hak_bawa_pulang' => 'boolean',
    ];

    // Helper label emoji untuk jenis aset
    public function getJenisIconAttribute(): string
    {
        $j = strtolower($this->jenis ?? '');
        if (str_contains($j, 'laptop') || str_contains($j, 'notebook')) return '💻';
        if (str_contains($j, 'charger') || str_contains($j, 'adaptor')) return '🔌';
        if (str_contains($j, 'mouse')) return '🖱️';
        if (str_contains($j, 'lan') || str_contains($j, 'extender') || str_contains($j, 'adapter')) return '🌐';
        if (str_contains($j, 'headset') || str_contains($j, 'earphone') || str_contains($j, 'headphone')) return '🎧';
        if (str_contains($j, 'audio') || str_contains($j, 'jack') || str_contains($j, 'sound')) return '🎙️';
        if (str_contains($j, 'hp') || str_contains($j, 'root') || str_contains($j, 'phone') || str_contains($j, 'smartphone') || str_contains($j, 'handphone')) return '📱';
        return '📦';
    }
}

