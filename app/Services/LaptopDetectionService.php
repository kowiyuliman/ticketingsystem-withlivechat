<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Asset;
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

        $candidateNames = [];

        // 1. Priority 1: Explicit Override / URL Query / Cookie / Session
        $savedSn = $overrideHostname ?? request()->query('laptop_sn') ?? request()->cookie('mptb_laptop_sn') ?? session('mptb_laptop_sn');
        if ($savedSn && trim($savedSn) !== '') {
            $candidateNames[] = trim($savedSn);
        }

        // 2. Priority 2: Custom Client Header (if reverse proxy / agent sends it)
        if ($headerDevice = request()->header('X-Device-Name')) {
            $candidateNames[] = trim($headerDevice);
        }

        // 3. Priority 3: Network Hostname / Computer Name Detection via DNS
        if ($rawIp === '127.0.0.1' || $rawIp === '::1') {
            $localHost = gethostname();
            if ($localHost) {
                $candidateNames[] = $localHost;
            }
        } else {
            $networkHost = @gethostbyaddr($rawIp);
            if ($networkHost && $networkHost !== $rawIp && !filter_var($networkHost, FILTER_VALIDATE_IP)) {
                $candidateNames[] = $networkHost;
            }
        }

        // 4. Priority 4: Memory from previous tickets submitted by this IP address
        if (empty($candidateNames)) {
            $prevTicket = Ticket::where('ip_address', $ip)
                ->whereNotNull('nomor_laptop')
                ->where(function ($q) {
                    $q->where('nomor_laptop', 'LIKE', 'LAP%')
                      ->orWhere('nomor_laptop', 'LIKE', '%LAP%');
                })
                ->latest()
                ->first();

            if ($prevTicket && $prevTicket->nomor_laptop) {
                $candidateNames[] = $prevTicket->nomor_laptop;
            }
        }

        // 5. Match candidates against `inventories`
        $inventory = null;
        $matchedSn = null;

        foreach ($candidateNames as $candidate) {
            $inventory = $this->findInventoryByDeviceName($candidate);
            if ($inventory) {
                $matchedSn = $inventory->sn;
                break;
            }
        }

        if ($inventory) {
            $hostname = $inventory->sn;
            $namaUser = $inventory->pengguna ?: ('Pengguna ' . $inventory->sn);
            $department = $inventory->department ?? '-';
            $noWhatsapp = $inventory->kontak ?? '-';
            $isDetected = true;
        } else {
            // If candidate has an explicit LAP string or user typed it, preserve it
            $firstCandidate = !empty($candidateNames) ? $candidateNames[0] : null;
            if ($firstCandidate && !filter_var($firstCandidate, FILTER_VALIDATE_IP)) {
                $normalizedFirst = $this->normalizeLapCode($firstCandidate);
                $hostname = $normalizedFirst ?: strtoupper($firstCandidate);
                $namaUser = 'Pengguna ' . $hostname;
                $department = '-';
                $noWhatsapp = '-';
                $isDetected = false;
            } else {
                $hostname = null;
                $namaUser = null;
                $department = '-';
                $noWhatsapp = '-';
                $isDetected = false;
            }
        }

        // Find associated user in system if exists
        $user = null;
        if ($namaUser) {
            $user = User::where('name', $namaUser)->first();
        }

        return [
            'ip_address'   => $ip,
            'hostname'     => $hostname,
            'user_id'      => $user?->id,
            'nama_user'    => $namaUser,
            'department'   => $department,
            'no_whatsapp'  => $noWhatsapp,
            'inventory'    => $inventory,
            'is_detected'  => $isDetected,
        ];
    }

    /**
     * Find Inventory by Device Name / Hostname / LAP Number variations
     */
    public function findInventoryByDeviceName(string $rawName): ?Inventory
    {
        $rawName = trim($rawName);
        if (empty($rawName) || filter_var($rawName, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Strip domain suffix (e.g. LAP-0303.mptb.lan -> LAP-0303)
        $cleanName = strtoupper(explode('.', $rawName)[0]);

        // List of candidate variations
        $variations = [
            $rawName,
            $cleanName,
            str_replace(['_', ' '], '-', $cleanName),
            str_replace(['-', '_', ' '], '', $cleanName),
        ];

        // Try LAP regex extraction: LAP[-_ ]?(\d+) or UP-LAP[-_ ]?(\d+)
        if (preg_match('/(?:UP-)?LAP[-_ ]?(\d+)/i', $cleanName, $matches)) {
            $digits = $matches[1];
            $isUp = str_starts_with($cleanName, 'UP-') || str_contains($cleanName, 'UP-LAP');
            $prefix = $isUp ? 'UP-LAP-' : 'LAP-';

            // 4-digit zero-padded (e.g. LAP-0303)
            $variations[] = $prefix . str_pad($digits, 4, '0', STR_PAD_LEFT);
            // 3-digit zero-padded (e.g. LAP-303 or UP-LAP-009)
            $variations[] = $prefix . str_pad($digits, 3, '0', STR_PAD_LEFT);
            // Non-padded (e.g. LAP-303)
            $variations[] = $prefix . (int)$digits;
            // Without hyphen (e.g. LAP0303)
            $variations[] = str_replace('-', '', $prefix) . str_pad($digits, 4, '0', STR_PAD_LEFT);
        }

        $variations = array_unique(array_filter($variations));

        // 1. Direct exact search in SN
        foreach ($variations as $var) {
            $inv = Inventory::whereRaw('UPPER(sn) = ?', [strtoupper($var)])->first();
            if ($inv) {
                return $inv;
            }
        }

        // 2. Search where SN without hyphens matches variation without hyphens
        foreach ($variations as $var) {
            $cleanVar = str_replace(['-', '_', ' '], '', strtoupper($var));
            if (strlen($cleanVar) >= 4) {
                $inv = Inventory::whereRaw('REPLACE(REPLACE(UPPER(sn), "-", ""), " ", "") = ?', [$cleanVar])->first();
                if ($inv) {
                    return $inv;
                }
            }
        }

        // 3. Search in `keterangan` or `sn` LIKE
        foreach ($variations as $var) {
            if (strlen($var) >= 5) {
                $inv = Inventory::where('sn', 'LIKE', "%{$var}%")
                    ->orWhere('keterangan', 'LIKE', "%{$var}%")
                    ->first();
                if ($inv) {
                    return $inv;
                }
            }
        }

        // 4. Search by pengguna name if the device name resembles an employee name
        $invByName = Inventory::whereRaw('UPPER(pengguna) = ?', [strtoupper($cleanName)])
            ->where(function ($q) {
                $q->where('jenis', 'LIKE', '%laptop%')
                  ->orWhere('sn', 'LIKE', 'LAP%');
            })
            ->first();

        if ($invByName) {
            return $invByName;
        }

        return null;
    }

    /**
     * Normalize string to LAP-XXXX format if it has digits
     */
    public function normalizeLapCode(string $input): ?string
    {
        $input = trim(strtoupper($input));
        if (preg_match('/(?:UP-)?LAP[-_ ]?(\d+)/i', $input, $matches)) {
            $digits = $matches[1];
            $isUp = str_starts_with($input, 'UP-') || str_contains($input, 'UP-LAP');
            $prefix = $isUp ? 'UP-LAP-' : 'LAP-';
            return $prefix . str_pad($digits, 4, '0', STR_PAD_LEFT);
        }
        return null;
    }
}
