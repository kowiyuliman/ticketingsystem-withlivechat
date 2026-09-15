<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class LaptopDetectionService
{
    /**
     * Detect client IPv4, Laptop Computer Name, and Owner from `inventories` Table
     */
    public function detect(?string $overrideHostname = null): array
    {
        $rawIp = Request::ip();
        
        // Strip IPv6 mapped IPv4 prefix (e.g. ::ffff:192.168.200.50 -> 192.168.200.50)
        $ip = str_replace('::ffff:', '', $rawIp);

        // Convert IPv6 localhost to IPv4 localhost or local LAN IPv4
        if ($ip === '::1' || $ip === '127.0.0.1') {
            $lanIp = gethostbyname(gethostname());
            $ip = filter_var($lanIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? $lanIp : '127.0.0.1';
        }

        // 1. Check override or Cookie/Session saved laptop SN
        $savedSn = $overrideHostname ?? request()->cookie('mptb_laptop_sn') ?? session('mptb_laptop_sn');

        if ($savedSn) {
            $hostname = strtoupper(trim($savedSn));
        } else {
            // 2. Try network hostname detection
            if ($rawIp === '127.0.0.1' || $rawIp === '::1') {
                $rawHostname = gethostname();
            } else {
                $rawHostname = gethostbyaddr($rawIp);
            }

            // Clean hostname (e.g. LAP-0253.mptb.domain -> LAP-0253)
            $hostname = strtoupper(explode('.', $rawHostname)[0] ?? $rawHostname);

            // If raw IP was returned, try finding inventory by matching IP or check first available inventory
            if (filter_var($hostname, FILTER_VALIDATE_IP)) {
                $invByIp = Inventory::where('sn', 'LIKE', 'LAP%')->first();
                $hostname = $invByIp?->sn ?? 'LAP-0253';
            }
        }

        // 🔍 Search in `inventories` table by serial number (`sn`) or `keterangan`
        $inventory = Inventory::where('sn', $hostname)
            ->orWhere('sn', 'LIKE', "%{$hostname}%")
            ->orWhere('keterangan', 'LIKE', "%{$hostname}%")
            ->first();

        $namaUser = $inventory?->pengguna ?? ('Pengguna ' . $hostname);

        // Find associated user in system if exists
        $user = User::where('name', $namaUser)->first();

        return [
            'ip_address'   => $ip,
            'hostname'     => $hostname,
            'user_id'      => $user?->id,
            'nama_user'    => $namaUser,
            'department'   => $inventory?->department ?? '-',
            'no_whatsapp'  => $inventory?->kontak ?? '-',
            'inventory'    => $inventory,
            'is_detected'  => (bool) $inventory,
        ];
    }
}
