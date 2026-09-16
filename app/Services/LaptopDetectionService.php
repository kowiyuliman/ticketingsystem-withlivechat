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

        $hostname = null;
        $inventory = null;

        // 1. Check override parameter, Query param, Cookie, Session, or Header
        $savedSn = $overrideHostname 
            ?? request()->query('laptop_sn')
            ?? request()->cookie('mptb_laptop_sn') 
            ?? session('mptb_laptop_sn')
            ?? request()->header('X-Laptop-SN');

        if (!empty($savedSn)) {
            $candidate = strtoupper(trim($savedSn));
            
            // Search in inventories by exact SN or partial SN or Pengguna name
            $inventory = Inventory::where('sn', $candidate)
                ->orWhere('sn', 'LIKE', "%{$candidate}%")
                ->orWhereRaw('LOWER(TRIM(pengguna)) = ?', [strtolower(trim($candidate))])
                ->orWhere('keterangan', 'LIKE', "%{$candidate}%")
                ->first();

            if ($inventory) {
                $hostname = $inventory->sn;
            } else {
                $hostname = $candidate;
            }
        }

        // 2. If not saved in cookie/session, try network hostname detection via reverse DNS / NetBIOS
        if (!$inventory) {
            $rawHostname = null;
            if ($rawIp === '127.0.0.1' || $rawIp === '::1') {
                $rawHostname = gethostname();
            } else {
                $rawHostname = @gethostbyaddr($rawIp);
            }

            if (!empty($rawHostname)) {
                // Clean hostname (e.g. LAP-0253.mptb.domain -> LAP-0253)
                $cleanHost = strtoupper(explode('.', $rawHostname)[0] ?? $rawHostname);

                // Ensure it's not returning just the IP address and not generic localhost
                if (!filter_var($cleanHost, FILTER_VALIDATE_IP) && $cleanHost !== 'UNKNOWN') {
                    // Try to match LAP-xxxx pattern if contained in hostname
                    if (preg_match('/LAP-?\d+/i', $cleanHost, $matches)) {
                        $matchedLap = strtoupper(str_replace('LAP', 'LAP-', str_replace('-', '', $matches[0])));
                        $inv = Inventory::where('sn', $matchedLap)->first();
                        if ($inv) {
                            $inventory = $inv;
                            $hostname = $inv->sn;
                        }
                    }

                    if (!$inventory) {
                        $inv = Inventory::where('sn', $cleanHost)
                            ->orWhere('sn', 'LIKE', "%{$cleanHost}%")
                            ->orWhere('keterangan', 'LIKE', "%{$cleanHost}%")
                            ->first();

                        if ($inv) {
                            $inventory = $inv;
                            $hostname = $inv->sn;
                        } else {
                            // Valid hostname string from network but not in DB
                            $hostname = $cleanHost;
                        }
                    }
                }
            }
        }

        $isDetected = (bool) $inventory;
        $namaUser = $inventory?->pengguna ?? ($hostname ? ('Pengguna ' . $hostname) : 'Pilih Laptop Anda');

        // Find associated user in ticketing system if exists
        $user = null;
        if ($inventory?->pengguna) {
            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($inventory->pengguna))])->first();
        }

        return [
            'ip_address'   => $ip,
            'hostname'     => $hostname ?: 'BELUM DIPILIH',
            'raw_hostname' => $hostname,
            'user_id'      => $user?->id,
            'nama_user'    => $namaUser,
            'department'   => $inventory?->department ?? '-',
            'no_whatsapp'  => $inventory?->kontak ?? '-',
            'inventory'    => $inventory,
            'is_detected'  => $isDetected,
        ];
    }
}
