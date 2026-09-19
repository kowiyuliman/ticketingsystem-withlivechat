<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Asset;
use App\Models\TicketComment;
use App\Models\TicketTimeline;
use App\Notifications\NewTicketNotification;
use App\Services\LaptopDetectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * User Portal: Main Page with Auto-Detection & 3 Tabs (Report, My Tickets, Assigned Assets)
     */
    public function portal(Request $request)
    {
        $overrideLaptop = $request->query('laptop_sn') ?? $request->cookie('mptb_laptop_sn') ?? session('mptb_laptop_sn');

        $detectionService = new LaptopDetectionService();
        $detection = $detectionService->detect($overrideLaptop);

        $hostname = $detection['hostname'];
        $ip = $detection['ip_address'];

        // Automatically persist detected laptop in cookie, session & cache if recognized
        if ($detection['is_detected'] && !empty($hostname) && !in_array($hostname, ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN'])) {
            \Illuminate\Support\Facades\Cookie::queue('mptb_laptop_sn', $hostname, 525600);
            session(['mptb_laptop_sn' => $hostname]);
            \Illuminate\Support\Facades\Cache::put("laptop_ip_mapping_{$ip}", $hostname, now()->addDays(365));
        }

        // Get available laptop list from inventories for switching/selection
        $availableLaptops = \App\Models\Inventory::where(function ($q) {
            $q->where('sn', 'LIKE', 'LAP%')
              ->orWhere('jenis', 'LIKE', '%laptop%');
        })
        ->orderBy('sn')
        ->get(['id', 'sn', 'pengguna', 'department', 'merk', 'lokasi']);

        // Query tickets STRICTLY for this specific laptop SN (if valid)
        $tickets = collect();
        $isRecognizedLaptop = !empty($hostname) && !in_array($hostname, ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN']);
        if ($isRecognizedLaptop) {
            $tickets = Ticket::where('nomor_laptop', $hostname)
                ->latest()
                ->get();
        }

        // Get all registered assets assigned to this Laptop/User (Laptop, Charger, Mouse, Headset, LAN Extender, etc.)
        $inventoryAssets = collect();
        $userName = $detection['inventory']?->pengguna ?? $detection['nama_user'];

        if (!empty($userName) && !str_starts_with($userName, 'Pengguna ') && !str_starts_with($userName, 'Pilih Laptop') && $userName !== 'Pengguna Baru') {
            $inventoryAssets = \App\Models\Inventory::where(function ($q) use ($userName, $hostname, $isRecognizedLaptop) {
                $q->whereRaw('LOWER(TRIM(pengguna)) = ?', [strtolower(trim($userName))]);
                if ($isRecognizedLaptop) {
                    $q->orWhere('sn', $hostname)
                      ->orWhere('keterangan', 'LIKE', "%{$hostname}%");
                }
            })->get();
        } elseif ($isRecognizedLaptop) {
            $inventoryAssets = \App\Models\Inventory::where('sn', $hostname)
                ->orWhere('keterangan', 'LIKE', "%{$hostname}%")
                ->get();
        }

        $tableAssets = collect();
        if ($isRecognizedLaptop) {
            $tableQuery = Asset::where('hostname', $hostname)
                ->orWhere('serial_number', $hostname);

            if ($detection['user_id']) {
                $tableQuery->orWhereHas('assignments', function ($q) use ($detection) {
                    $q->where('user_id', $detection['user_id'])->whereNull('returned_at');
                });
            }
            $tableAssets = $tableQuery->get();
        }

        // Merge both sources, deduplicate, and sort by asset priority
        $myAssets = $inventoryAssets->concat($tableAssets)->unique(function ($item) {
            $sn = $item->sn ?? $item->serial_number ?? $item->asset_code ?? $item->id;
            $type = $item->jenis ?? $item->type ?? 'asset';
            return strtolower($sn . '_' . $type);
        })->sortBy(function ($item) {
            $j = strtolower($item->jenis ?? $item->type ?? '');
            if (str_contains($j, 'laptop') || str_contains($j, 'notebook')) return 1;
            if (str_contains($j, 'charger') || str_contains($j, 'adaptor')) return 2;
            if (str_contains($j, 'mouse')) return 3;
            if (str_contains($j, 'headset') || str_contains($j, 'earphone')) return 4;
            if (str_contains($j, 'lan') || str_contains($j, 'extender')) return 5;
            return 6;
        })->values();

        return view('user.portal', compact('detection', 'tickets', 'myAssets', 'availableLaptops'));
    }

    public function setLaptop(Request $request)
    {
        $inputSn = trim($request->input('nomor_laptop', ''));
        $tab = $request->input('tab', 'history');
        $detectionService = new LaptopDetectionService();
        $inventory = $detectionService->findInventoryByDeviceName($inputSn);

        if (!$inventory) {
            return redirect()->route('portal', ['tab' => $tab])
                ->with('error', 'Nomor laptop "' . $inputSn . '" tidak valid atau tidak terdaftar di database inventaris. Silakan periksa kembali nomor laptop Anda.');
        }

        $sn = $inventory->sn;
        $nama = $inventory->pengguna ?: $inventory->sn;
        $msg = 'Perangkat berhasil disetel ke ' . $sn . ' (' . $nama . ')';

        \Illuminate\Support\Facades\Cookie::queue('mptb_laptop_sn', $sn, 525600);
        session(['mptb_laptop_sn' => $sn]);

        $rawIp = $request->ip();
        $ip = str_replace('::ffff:', '', $rawIp);
        \Illuminate\Support\Facades\Cache::put("laptop_ip_mapping_{$ip}", $sn, now()->addDays(365));

        return redirect()->route('portal', ['tab' => $tab])
            ->withCookie(cookie()->forever('mptb_laptop_sn', $sn))
            ->with('success', $msg);
    }

    public function index()
    {
        return redirect()->route('portal', ['tab' => 'history']);
    }

    public function create()
    {
        return redirect()->route('portal');
    }

    /**
     * Store new instant ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'deskripsi' => 'required|string|min:5',
            'kategori'  => 'required|string|in:hardware,software,network,other',
        ], [
            'kategori.required' => 'Silakan pilih salah satu kategori kendala terlebih dahulu.',
            'deskripsi.required' => 'Ceritakan kendala yang Anda alami.',
            'deskripsi.min'      => 'Deskripsi kendala minimal 5 karakter.',
        ]);

        $rawNomorLaptop = $request->input('nomor_laptop');
        $detectionService = new LaptopDetectionService();
        $detection = $detectionService->detect($rawNomorLaptop);

        $kategori = $request->input('kategori');
        $ticket_code = Ticket::generateTicketCode($kategori);
        $screenshot = null;

        if ($request->hasFile('screenshot')) {
            $screenshot = $request->file('screenshot')->store('tickets', 'public');
        }

        $nomorLaptop = $rawNomorLaptop ?: $detection['hostname'];
        if (in_array($nomorLaptop, ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN']) || empty($nomorLaptop)) {
            $nomorLaptop = 'LAP-UNKNOWN';
        } else {
            \Illuminate\Support\Facades\Cookie::queue('mptb_laptop_sn', $nomorLaptop, 525600);
            session(['mptb_laptop_sn' => $nomorLaptop]);

            $ipAddress = $request->input('ip_address') ?: $detection['ip_address'];
            if ($ipAddress) {
                \Illuminate\Support\Facades\Cache::put("laptop_ip_mapping_{$ipAddress}", $nomorLaptop, now()->addDays(365));
            }
        }

        $namaUser = Auth::check() ? Auth::user()->name : ($request->input('nama') ?: $detection['nama_user']);
        if (str_starts_with($namaUser, 'Pilih Laptop') || $namaUser === 'Pengguna Baru') {
            $namaUser = 'Pengguna ' . ($nomorLaptop !== 'LAP-UNKNOWN' ? $nomorLaptop : 'Baru');
        }

        $userId = Auth::id() ?? $detection['user_id'] ?? null;
        if (!$userId) {
            $existingUser = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($namaUser))])->first();
            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                $user = User::create([
                    'name'     => $namaUser,
                    'username' => 'guest_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nomorLaptop ?: 'user')) . '_' . rand(1000, 9999),
                    'password' => bcrypt('password'),
                    'role'     => 'user',
                ]);
                $userId = $user->id;
            }
        }
        $ipAddress = $request->input('ip_address') ?: $detection['ip_address'];

        $ticket = Ticket::create([
            'ticket_code'   => $ticket_code,
            'user_id'       => $userId,
            'nama'          => $namaUser,
            'nomor_laptop'  => $nomorLaptop,
            'ip_address'    => $ipAddress,
            'nomor_meja'    => $request->input('nomor_meja', '-'),
            'nomor_ruangan' => $request->input('nomor_ruangan', '-'),
            'no_whatsapp'   => $request->input('no_whatsapp', '-'),
            'deskripsi'     => $request->deskripsi,
            'screenshot'    => $screenshot,
            'status'        => 'open',
            'kategori'      => $kategori,
            'created_by'    => $userId,
        ]);

        Cache::flush();

        // Notify admins safely without blocking user submission
        try {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewTicketNotification($ticket));
            }
        } catch (\Throwable $e) {
            // Log notification error if broadcast is offline
        }

        \Illuminate\Support\Facades\Cookie::queue('mptb_laptop_sn', $nomorLaptop, 525600);
        session(['mptb_laptop_sn' => $nomorLaptop]);

        return redirect()->route('ticket.show', $ticket->id)
            ->withCookie(cookie()->forever('mptb_laptop_sn', $nomorLaptop))
            ->with('success', 'Pengaduan berhasil terkirim! Tiket #' . $ticket->ticket_code . ' sedang diproses tim IT.');
    }

    /**
     * Ticket Detail & Real-time Live Chat
     */
    public function show($id)
    {
        $ticket = Ticket::with(['histories', 'comments.user', 'user', 'technician'])->findOrFail($id);

        // Record user presence & mark admin messages as read
        if (!Auth::check() || Auth::user()->role === 'user') {
            \Illuminate\Support\Facades\Cache::put("ticket_{$id}_user_last_seen", now(), now()->addMinutes(2));
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', true)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => now()]);
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', true)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $adminLastSeen = \Illuminate\Support\Facades\Cache::get("ticket_{$id}_admin_last_seen");
        $isAdminActive = $adminLastSeen && \Carbon\Carbon::parse($adminLastSeen)->gt(now()->subSeconds(25));
        $adminName = $ticket->technician?->name ?? \Illuminate\Support\Facades\Cache::get("ticket_{$id}_admin_name") ?? 'Admin IT';

        return view('user.tickets.show', compact('ticket', 'isAdminActive', 'adminLastSeen', 'adminName'));
    }

    /**
     * Fetch comments as JSON for real-time live chat polling
     */
    public function fetchComments($id)
    {
        $ticket = Ticket::with(['comments.user', 'technician'])->findOrFail($id);

        $isAdminRequester = Auth::check() && in_array(Auth::user()->role, ['admin', 'technician']);

        if ($isAdminRequester) {
            // Admin polling: Update admin presence & mark unread user comments as read
            \Illuminate\Support\Facades\Cache::put("ticket_{$id}_admin_last_seen", now(), now()->addMinutes(2));
            \Illuminate\Support\Facades\Cache::put("ticket_{$id}_admin_name", Auth::user()->name, now()->addMinutes(2));
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', false)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => now()]);
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', false)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            // User polling: Update user presence & mark unread admin comments as read
            \Illuminate\Support\Facades\Cache::put("ticket_{$id}_user_last_seen", now(), now()->addMinutes(2));
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', true)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => now()]);
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', true)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $adminLastSeen = \Illuminate\Support\Facades\Cache::get("ticket_{$id}_admin_last_seen");
        $isAdminActive = $adminLastSeen && \Carbon\Carbon::parse($adminLastSeen)->gt(now()->subSeconds(25));
        $adminName = $ticket->technician?->name ?? \Illuminate\Support\Facades\Cache::get("ticket_{$id}_admin_name") ?? 'Tim IT Support';

        // If admin is active, mark unread user comments as delivered
        if ($isAdminActive) {
            \App\Models\TicketComment::where('ticket_id', $ticket->id)
                ->where('is_admin', false)
                ->whereNull('delivered_at')
                ->update([
                    'delivered_at' => now(),
                ]);
        }

        return response()->json([
            'ticket_code'          => $ticket->ticket_code,
            'status'               => $ticket->status,
            'status_label'         => str_replace('_', ' ', $ticket->status),
            'status_reason'        => $ticket->status_reason ?? $ticket->reason_text ?? null,
            'kategori'             => $ticket->kategori,
            'technician_name'      => $adminName,
            'is_closed'            => in_array($ticket->status, ['closed', 'cancelled']),
            'is_admin_active'      => (bool)$isAdminActive,
            'admin_last_seen_text' => $adminLastSeen ? \Carbon\Carbon::parse($adminLastSeen)->format('H:i') : null,
            'comments'             => $ticket->comments()->with('user')->get()->map(function ($comment) use ($ticket) {
                $isAdmin = (bool)$comment->is_admin;
                $isUser = !$isAdmin;
                $senderName = $isAdmin ? ($comment->user?->name ?? 'Admin IT') : ($ticket->nama ?: 'Pengguna');
                return [
                    'id'             => $comment->id,
                    'comment'        => $comment->comment ?? '',
                    'attachment_url' => $comment->attachment ? asset('storage/' . $comment->attachment) : null,
                    'is_user'        => $isUser,
                    'user_name'      => $senderName,
                    'created_at'     => $comment->created_at->format('H:i'),
                    'read_status'    => $comment->read_status, // 'sent', 'delivered', 'read'
                ];
            }),
        ]);
    }

    /**
     * Add comment to ticket (Live Chat with optional image attachment)
     */
    public function comment(Request $request, $id)
    {
        $request->validate([
            'comment'    => 'nullable|string',
            'attachment' => 'nullable|image|max:10240',
        ]);

        $ticket = Ticket::findOrFail($id);

        if (in_array($ticket->status, ['closed', 'cancelled'])) {
            return back()->with('error', 'Tiket ini sudah ditutup. Silakan buat tiket baru.');
        }
        $userId = Auth::id() ?? $ticket->user_id;
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tickets/comments', 'public');
        } elseif ($request->filled('attachment_base64')) {
            $base64Data = $request->input('attachment_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]) === 'jpeg' ? 'jpg' : strtolower($type[1]);
                $decoded = base64_decode($data);
                if ($decoded !== false) {
                    $fileName = 'comment_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $filePath = 'tickets/comments/' . $fileName;
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filePath, $decoded);
                    $attachmentPath = $filePath;
                }
            }
        }

        if (empty(trim($request->comment ?? '')) && empty($attachmentPath)) {
            return back()->with('error', 'Pesan atau foto tidak boleh kosong.');
        }

        $comment = TicketComment::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $ticket->user_id ?? Auth::id(),
            'is_admin'   => false,
            'comment'    => $request->comment ?? '',
            'attachment' => $attachmentPath,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id'             => $comment->id,
                    'comment'        => $comment->comment ?? '',
                    'attachment_url' => $comment->attachment ? asset('storage/' . $comment->attachment) : null,
                    'is_user'        => true,
                    'user_name'      => $ticket->nama ?: 'Pengguna',
                    'created_at'     => $comment->created_at->format('H:i'),
                    'read_status'    => $comment->read_status,
                ],
            ]);
        }

        return back();
    }

    /**
     * Update ticket status (Admin/Tech)
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;

        $ticket->update([
            'status'      => $request->status,
            'kategori'    => $request->kategori,
            'assigned_to' => $request->assigned_to,
        ]);

        if ($oldStatus != 'on_progress' && $request->status == 'on_progress') {
            $ticket->started_at = Carbon::now();
        }

        if ($oldStatus != 'closed' && $request->status == 'closed') {
            $ticket->resolved_at = Carbon::now();
        }

        $ticket->save();

        TicketTimeline::create([
            'ticket_id'   => $ticket->id,
            'status'      => $request->status,
            'description' => 'Status diubah ke ' . $request->status,
        ]);

        return back()->with('success', 'Ticket berhasil diupdate');
    }
}
