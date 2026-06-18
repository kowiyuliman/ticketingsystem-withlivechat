<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AssetsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip jika type kosong
        if (empty($row['type'])) {
            return null;
        }

        // Normalisasi type
        $type = strtolower(trim($row['type']));
        // Ganti spasi/karakter lain jika user salah ketik
        $type = str_replace(' ', '_', $type);

        $validTypes = ['laptop', 'charger', 'mouse', 'lan_adapter', 'headset', 'usb_audio'];
        if (!in_array($type, $validTypes)) {
            return null;
        }

        // Cek serial number unik
        $serialNumber = !empty($row['serial_number']) ? trim($row['serial_number']) : null;
        if ($serialNumber && Asset::where('serial_number', $serialNumber)->exists()) {
            return null;
        }

        // Normalisasi kondisi & status
        $condition = !empty($row['condition']) ? strtolower(trim($row['condition'])) : 'good';
        if (!in_array($condition, ['good', 'repair', 'damaged'])) {
            $condition = 'good';
        }

        $status = 'available';
        if ($condition === 'repair') {
            $status = 'in_repair';
        } elseif ($condition === 'damaged') {
            $status = 'damaged';
        }

        return new Asset([
            'type'          => $type,
            'brand'         => $row['brand'] ?? null,
            'model'         => $row['model'] ?? null,
            'serial_number' => $serialNumber,
            'condition'     => $condition,
            'status'        => $status,
            'notes'         => $row['notes'] ?? null,
        ]);
    }
}
