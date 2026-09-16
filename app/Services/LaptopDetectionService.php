<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;

class LaptopDetectionService
{
    /**
     * Detect client IPv4, Laptop Computer Name, and Owner from `inventories` Table (Read-Only)
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

        // 1. Priority 1: Explicit Override / URL Query / Cookie / Session / Header
        $savedSn = $overrideHostname 
            ?? request()->query('laptop_sn') 
            ?? request()->cookie('mptb_laptop_sn') 
            ?? session('mptb_laptop_sn')
            ?? request()->header('X-Device-Name')
            ?? request()->header('X-Laptop-SN');

        if ($savedSn && trim($savedSn) !== '' && !in_array(strtoupper(trim($savedSn)), ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN'])) {
            $candidateNames[] = trim($savedSn);
        }

        // 2. Priority 2: Automated Network Computer Name Detection (NetBIOS UDP 137 + DNS PTR)
        $networkHost = $this->resolveClientHostname($ip, $rawIp);
        if (!empty($networkHost) && !in_array(strtoupper($networkHost), ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN', 'UNKNOWN'])) {
            $candidateNames[] = $networkHost;
        }

        // 3. Priority 3: Memory from previous tickets submitted from this IP address
        if (empty($candidateNames)) {
            $prevTicket = Ticket::where('ip_address', $ip)
                ->whereNotNull('nomor_laptop')
                ->where(function ($q) {
                    $q->where('nomor_laptop', 'LIKE', 'LAP%')
                      ->orWhere('nomor_laptop', 'LIKE', '%LAP%');
                })
                ->latest()
                ->first();

            if ($prevTicket && $prevTicket->nomor_laptop && !in_array($prevTicket->nomor_laptop, ['LAP-UNKNOWN', 'BELUM DIPILIH', 'BELUM TERDETEKSI'])) {
                $candidateNames[] = $prevTicket->nomor_laptop;
            }
        }

        // 4. Match candidates against `inventories`
        $inventory = null;
        $hostname = null;

        foreach ($candidateNames as $candidate) {
            $inventory = $this->findInventoryByDeviceName($candidate);
            if ($inventory) {
                $hostname = $inventory->sn;
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
            $firstCandidate = !empty($candidateNames) ? $candidateNames[0] : null;
            if ($firstCandidate && !filter_var($firstCandidate, FILTER_VALIDATE_IP)) {
                $normalized = $this->normalizeLapCode($firstCandidate);
                $hostname = $normalized ?: strtoupper($firstCandidate);
                $namaUser = 'Pengguna ' . $hostname;
                $department = '-';
                $noWhatsapp = '-';
                $isDetected = false;
            } else {
                $hostname = null;
                $namaUser = 'Pengguna Baru';
                $department = '-';
                $noWhatsapp = '-';
                $isDetected = false;
            }
        }

        // Find associated user in system if exists
        $user = null;
        if ($namaUser && $namaUser !== 'Pengguna Baru') {
            $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($namaUser))])->first();
        }

        return [
            'ip_address'   => $ip,
            'hostname'     => $hostname ?: 'BELUM TERDETEKSI',
            'raw_hostname' => $hostname,
            'user_id'      => $user?->id,
            'nama_user'    => $namaUser,
            'department'   => $department,
            'no_whatsapp'  => $noWhatsapp,
            'inventory'    => $inventory,
            'is_detected'  => $isDetected,
        ];
    }

    /**
     * Resolve Windows Computer Name via NetBIOS (UDP 137) or DNS PTR with short timeout & caching
     */
    public function resolveClientHostname(string $ip, ?string $rawIp = null): ?string
    {
        if ($ip === '127.0.0.1' || $rawIp === '127.0.0.1' || $rawIp === '::1') {
            return gethostname();
        }

        return Cache::remember("laptop_host_{$ip}", 300, function () use ($ip) {
            // A. Query NetBIOS Node Status (UDP Port 137 - Standard Windows LAN resolution)
            $netbiosName = $this->queryNetbios($ip, 0.25);
            if (!empty($netbiosName) && $netbiosName !== 'UNKNOWN' && !filter_var($netbiosName, FILTER_VALIDATE_IP)) {
                return $netbiosName;
            }

            // B. Fallback to DNS PTR lookup
            $dns = @gethostbyaddr($ip);
            if (!empty($dns) && $dns !== $ip) {
                $clean = strtoupper(explode('.', $dns)[0] ?? $dns);
                if (!filter_var($clean, FILTER_VALIDATE_IP) && $clean !== 'UNKNOWN') {
                    return $clean;
                }
            }

            return null;
        });
    }

    /**
     * Send NetBIOS Node Status Query over UDP 137
     */
    protected function queryNetbios(string $ip, float $timeout = 0.25): ?string
    {
        $fp = @fsockopen("udp://$ip", 137, $errno, $errstr, $timeout);
        if (!$fp) return null;

        $seconds = (int) $timeout;
        $micro = (int) (($timeout - $seconds) * 1000000);
        stream_set_timeout($fp, $seconds, $micro);

        // Standard RFC 1002 NetBIOS Node Status Request Packet
        $packet = "\x81\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x20\x43\x4b\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x41\x00\x00\x21\x00\x01";
        
        @fwrite($fp, $packet);
        $response = @fread($fp, 1024);
        @fclose($fp);

        if (!$response || strlen($response) < 57) {
            return null;
        }

        $numNames = ord($response[56]);
        for ($i = 0; $i < $numNames; $i++) {
            $offset = 57 + ($i * 18);
            if (strlen($response) < $offset + 18) break;
            $name = trim(substr($response, $offset, 15));
            $type = ord($response[$offset + 15]);
            $flags = ord($response[$offset + 16]);

            // Type 0x00 = Workstation / Computer Name, Type 0x20 = Server Service
            if (!empty($name) && ($type === 0x00 || $type === 0x20) && !($flags & 0x80)) {
                return strtoupper($name);
            }
        }

        // Fallback: take first valid non-empty name from response
        if (strlen($response) >= 72) {
            $name = trim(substr($response, 57, 15));
            if (!empty($name)) {
                return strtoupper($name);
            }
        }

        return null;
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
