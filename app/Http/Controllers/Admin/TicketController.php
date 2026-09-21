<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TicketUpdateNotification;
use App\Models\TicketTimeline;
use Carbon\Carbon;
use App\Models\User;



class TicketController extends Controller
{
    public function index(Request $request)
    {
        $unreadCommentCountScope = function ($q) {
            $q->where('is_admin', false)->whereNull('read_at');
        };

        $tickets_open = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->where('status','open')
            ->orderBy('created_at','desc')
            ->paginate(20);

        $tickets_progress = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->where('status','on_progress')
            ->orderBy('started_at','desc')
            ->paginate(20);

        $tickets_pending = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->where('status','pending')
            ->orderBy('updated_at','desc')
            ->paginate(20);

        $tickets_closed = Ticket::where('status','closed')
            ->orderBy('resolved_at','desc')
            ->paginate(20);

        $tickets_cancel = Ticket::where('status','cancelled')
            ->latest()
            ->get();

        return view('admin.tickets.index', compact('tickets_open','tickets_progress','tickets_pending','tickets_closed','tickets_cancel'));
    }

    public function unreadCount(Request $request)
    {
        $openTicketsCount = Ticket::where('status', 'open')->count();
        
        $unreadChatsCount = TicketComment::where('is_admin', false)
            ->whereNull('read_at')
            ->count();

        $unreadTicketIds = TicketComment::where('is_admin', false)
            ->whereNull('read_at')
            ->pluck('ticket_id')
            ->unique()
            ->values();

        $totalUnread = $openTicketsCount + $unreadChatsCount;

        return response()->json([
            'has_unread'         => $totalUnread > 0,
            'total_unread'       => $totalUnread,
            'open_tickets_count' => $openTicketsCount,
            'unread_chats_count' => $unreadChatsCount,
            'unread_ticket_ids'  => $unreadTicketIds,
        ]);
    }

    public function fetchTickets(Request $request)
    {
        $unreadCommentCountScope = function ($q) {
            $q->where('is_admin', false)->whereNull('read_at');
        };

        $openTickets = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $progressTickets = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->with('technician')
            ->where('status', 'on_progress')
            ->orderBy('started_at', 'desc')
            ->take(50)
            ->get();

        $pendingTickets = Ticket::withCount(['comments as unread_comments_count' => $unreadCommentCountScope])
            ->with('technician')
            ->where('status', 'pending')
            ->orderBy('updated_at', 'desc')
            ->take(50)
            ->get();

        $closedTickets = Ticket::with('technician')
            ->where('status', 'closed')
            ->orderBy('resolved_at', 'desc')
            ->take(50)
            ->get();

        $cancelTickets = Ticket::where('status', 'cancelled')
            ->latest()
            ->take(50)
            ->get();

        $openCount = Ticket::where('status', 'open')->count();
        $progressCount = Ticket::where('status', 'on_progress')->count();
        $pendingCount = Ticket::where('status', 'pending')->count();
        $closedCount = Ticket::where('status', 'closed')->count();
        $cancelCount = Ticket::where('status', 'cancelled')->count();

        $csrfToken = csrf_token();

        return response()->json([
            'counts' => [
                'open'     => $openCount,
                'progress' => $progressCount,
                'pending'  => $pendingCount,
                'closed'   => $closedCount,
                'cancel'   => $cancelCount,
            ],
            'tickets' => [
                'open' => $openTickets->map(function ($t, $idx) use ($csrfToken) {
                    return [
                        'iteration'             => $idx + 1,
                        'id'                    => $t->id,
                        'ticket_code'           => $t->ticket_code,
                        'nama'                  => $t->nama,
                        'nomor_laptop'          => $t->nomor_laptop,
                        'ip_address'            => $t->ip_address,
                        'kategori'              => strtoupper($t->kategori ?? 'General'),
                        'deskripsi'             => $t->deskripsi,
                        'created_at_formatted'  => $t->created_at ? $t->created_at->format('d M Y, H:i') : '-',
                        'unread_comments_count' => (int)($t->unread_comments_count ?? 0),
                        'take_url'              => url('/admin/ticket/take/' . $t->id),
                        'show_url'              => url('/admin/ticket/show/' . $t->id),
                        'vnc_url'               => 'vnc://' . $t->ip_address,
                        'delete_url'            => url('/admin/ticket/delete/' . $t->id),
                        'csrf_token'            => $csrfToken,
                    ];
                }),
                'progress' => $progressTickets->map(function ($t, $idx) use ($csrfToken) {
                    return [
                        'iteration'             => $idx + 1,
                        'id'                    => $t->id,
                        'ticket_code'           => $t->ticket_code,
                        'nama'                  => $t->nama,
                        'nomor_laptop'          => $t->nomor_laptop,
                        'kategori'              => strtoupper($t->kategori ?? 'General'),
                        'deskripsi'             => $t->deskripsi,
                        'technician_name'       => $t->technician->name ?? '-',
                        'unread_comments_count' => (int)($t->unread_comments_count ?? 0),
                        'show_url'              => url('/admin/ticket/show/' . $t->id),
                        'delete_url'            => url('/admin/ticket/delete/' . $t->id),
                        'csrf_token'            => $csrfToken,
                    ];
                }),
                'pending' => $pendingTickets->map(function ($t, $idx) use ($csrfToken) {
                    return [
                        'iteration'             => $idx + 1,
                        'id'                    => $t->id,
                        'ticket_code'           => $t->ticket_code,
                        'nama'                  => $t->nama,
                        'nomor_laptop'          => $t->nomor_laptop,
                        'technician_name'       => $t->technician->name ?? '-',
                        'deskripsi'             => $t->deskripsi,
                        'reason_text'           => $t->reason_text,
                        'unread_comments_count' => (int)($t->unread_comments_count ?? 0),
                        'show_url'              => url('/admin/ticket/show/' . $t->id),
                        'delete_url'            => url('/admin/ticket/delete/' . $t->id),
                        'csrf_token'            => $csrfToken,
                    ];
                }),
                'closed' => $closedTickets->map(function ($t, $idx) use ($csrfToken) {
                    return [
                        'iteration'       => $idx + 1,
                        'id'              => $t->id,
                        'ticket_code'     => $t->ticket_code,
                        'nama'            => $t->nama,
                        'nomor_laptop'    => $t->nomor_laptop,
                        'technician_name' => $t->technician->name ?? '-',
                        'durasi_menit'    => $t->durasi_menit ?? '-',
                        'show_url'        => url('/admin/ticket/show/' . $t->id),
                        'delete_url'      => url('/admin/ticket/delete/' . $t->id),
                        'csrf_token'      => $csrfToken,
                    ];
                }),
                'cancel' => $cancelTickets->map(function ($t, $idx) use ($csrfToken) {
                    return [
                        'iteration'            => $idx + 1,
                        'id'                   => $t->id,
                        'ticket_code'          => $t->ticket_code,
                        'nama'                 => $t->nama,
                        'nomor_laptop'         => $t->nomor_laptop,
                        'deskripsi'            => $t->deskripsi,
                        'reason_text'          => $t->reason_text,
                        'updated_at_formatted' => $t->updated_at ? $t->updated_at->format('d M Y, H:i') : '-',
                        'show_url'             => url('/admin/ticket/show/' . $t->id),
                        'delete_url'           => url('/admin/ticket/delete/' . $t->id),
                        'csrf_token'           => $csrfToken,
                    ];
                }),
            ],
        ]);
    }

    public function show($id)
    {
        $ticket = Ticket::with('histories','comments.user','timelines','mergedChildren')->findOrFail($id);

        // Record admin active presence on this ticket
        \Illuminate\Support\Facades\Cache::put("ticket_{$id}_admin_last_seen", now(), now()->addMinutes(2));
        \Illuminate\Support\Facades\Cache::put("ticket_{$id}_admin_name", Auth::user()->name, now()->addMinutes(2));

        // Mark unread user comments as delivered and read by admin
        \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('is_admin', false)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);
        \App\Models\TicketComment::where('ticket_id', $ticket->id)
            ->where('is_admin', false)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function edit($id){
        $ticket = Ticket::with('comments.user','mergedChildren')->findOrFail($id);

        // PROTEKSI
        if($ticket->assigned_to != Auth::id()){
            return redirect()->back()->with('error','Bukan tiket kamu');
        }

        if($ticket->status == 'merged'){
            return redirect('/admin/tickets')
                ->with(
                    'error',
                    'Ticket merged tidak bisa diedit'
                );
        }

        $technicians = User::whereIn('role',['admin','technician'])->get();

        return view('admin.tickets.edit',compact('ticket','technicians'));
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->assigned_to && $ticket->assigned_to != Auth::id() && Auth::user()->role !== 'admin') {
            return redirect('/admin/tickets')->with('error', 'Bukan tiket kamu');
        }

        if (!$ticket->assigned_to) {
            $ticket->assigned_to = Auth::id();
        }

        $oldStatus = $ticket->status;
        $newStatus = ($oldStatus === 'closed') ? 'closed' : $request->input('status', $ticket->status);
        $keteranganInput = trim($request->input('keterangan', ''));

        $oldKategori = $ticket->kategori;
        $newKategori = $request->input('kategori', $ticket->kategori);
        $kategoriChanged = $newKategori && $newKategori !== $oldKategori;
        $oldTicketCode = $ticket->ticket_code;
        $newTicketCode = $ticket->ticket_code;

        if ($kategoriChanged) {
            $dateStr = $ticket->created_at ? $ticket->created_at->format('Ymd') : date('Ymd');
            $newTicketCode = Ticket::generateTicketCode($newKategori, $dateStr);
        }

        $ticket->update([
            'ticket_code'   => $newTicketCode,
            'status'        => $newStatus,
            'kategori'      => $newKategori,
            'status_reason' => !empty($keteranganInput) ? $keteranganInput : ($newStatus === 'pending' || $newStatus === 'cancelled' ? $ticket->status_reason : null),
        ]);

        \Illuminate\Support\Facades\Cache::flush();

        // SLA Tracking
        if ($oldStatus != 'on_progress' && $newStatus == 'on_progress') {
            $ticket->started_at = now();
        }

        if ($oldStatus != 'closed' && $newStatus == 'closed') {
            $ticket->resolved_at = now();
        }

        $ticket->save();

        $statusLabel = match($newStatus) {
            'pending' => 'Pending',
            'cancelled' => 'Cancelled',
            'closed' => 'Closed',
            'on_progress' => 'On Progress',
            'open' => 'Open',
            default => ucfirst($newStatus)
        };

        $historyKeterangan = !empty($keteranganInput)
            ? "Status diubah ke {$statusLabel}. Keterangan: {$keteranganInput}"
            : "Admin " . Auth::user()->name . " mengupdate tiket ke {$statusLabel}";

        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'status'     => $newStatus,
            'keterangan' => $historyKeterangan,
            'updated_by' => Auth::id()
        ]);

        // If category changed, log to history and notify live chat
        if ($kategoriChanged) {
            $oldKatName = ucfirst($oldKategori ?? 'other');
            $newKatName = ucfirst($newKategori);
            TicketHistory::create([
                'ticket_id'  => $ticket->id,
                'status'     => $newStatus,
                'keterangan' => "Admin " . Auth::user()->name . " mengubah kategori dari {$oldKatName} ke {$newKatName}. Nomor tiket diperbarui: #{$newTicketCode}",
                'updated_by' => Auth::id()
            ]);

            TicketComment::create([
                'ticket_id'  => $ticket->id,
                'user_id'    => Auth::id(),
                'is_admin'   => true,
                'comment'    => "🔄 Kategori kendala diubah dari [{$oldKatName}] menjadi [{$newKatName}]. Nomor tiket diperbarui menjadi #{$newTicketCode}",
                'attachment' => null,
            ]);
        }

        // Post status update reason to Live Chat stream if provided
        if (!empty($keteranganInput)) {
            TicketComment::create([
                'ticket_id'  => $ticket->id,
                'user_id'    => Auth::id(),
                'is_admin'   => true,
                'comment'    => "📌 Status tiket diubah menjadi [{$statusLabel}]. Keterangan: {$keteranganInput}",
                'attachment' => null,
            ]);
        }

        if ($ticket->user) {
            $ticket->user->notify(new TicketUpdateNotification($ticket));
        }

        // Redirect back to Admin Tickets List if ticket just transitioned to CLOSED
        if ($oldStatus !== 'closed' && $newStatus === 'closed') {
            return redirect('/admin/tickets')->with('success', 'Tiket #' . $ticket->ticket_code . ' berhasil ditutup.');
        }

        // Stay on current Live Chat page for category updates on closed ticket or other status updates
        return redirect()->back()->with('success', 'Status tiket berhasil diupdate menjadi ' . $statusLabel . ($kategoriChanged ? ' & nomor tiket diperbarui menjadi #' . $newTicketCode : ''));
    }

    public function takeTicket($id)
    {
        $ticket = Ticket::findOrFail($id);

        // hanya boleh ambil jika belum diambil orang lain
        if ($ticket->status != 'open' && $ticket->assigned_to && $ticket->assigned_to != Auth::id()) {
            return redirect()->back()->with('error', 'Ticket sudah diambil oleh teknisi lain');
        }

        $ticket->update([
            'status'      => 'on_progress',
            'assigned_to' => Auth::id(),
            'started_at'  => $ticket->started_at ?? now()
        ]);

        return redirect()->back()->with('success', 'Ticket berhasil diambil & mulai dikerjakan');
    }

    public function comment(Request $request, $id)
    {
        $request->validate([
            'comment'    => 'nullable|string',
            'attachment' => 'nullable|image|max:10240',
        ]);

        $ticket = Ticket::findOrFail($id);

        if ($ticket->status === 'closed') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Tiket telah ditutup. Percakapan telah diarsipkan.'], 422);
            }
            return back()->with('error', 'Tiket telah ditutup. Percakapan telah diarsipkan.');
        }
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
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Pesan atau foto tidak boleh kosong.'], 422);
            }
            return back()->with('error', 'Pesan atau foto tidak boleh kosong.');
        }

        $comment = TicketComment::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => Auth::id(),
            'is_admin'   => true,
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
                    'is_user'        => false,
                    'user_name'      => Auth::user()->name ?? 'Teknisi IT',
                    'created_at'     => $comment->created_at->format('H:i'),
                ]
            ]);
        }

        return back();
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticketCode = $ticket->ticket_code;

        // Delete comments and attachments
        foreach ($ticket->comments as $comment) {
            if ($comment->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($comment->attachment)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($comment->attachment);
            }
            $comment->delete();
        }

        // Delete screenshot if exists
        if ($ticket->screenshot && \Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->screenshot)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($ticket->screenshot);
        }

        // Delete histories and timelines
        TicketHistory::where('ticket_id', $ticket->id)->delete();
        TicketTimeline::where('ticket_id', $ticket->id)->delete();

        $ticket->delete();

        \Illuminate\Support\Facades\Cache::flush();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tiket #' . $ticketCode . ' berhasil dihapus.'
            ]);
        }

        return redirect()->back()->with('success', 'Tiket #' . $ticketCode . ' berhasil dihapus.');
    }


    public function TicketAdmin()
    {
        $tickets = Ticket::where('assigned_to', Auth::id())
            ->whereIn('status',['on_progress','pending'])
            ->orderBy('updated_at','desc')
            ->get();

        return view('admin.tickets.my_ticket', compact('tickets'));
    }



    public function reassign(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // hanya yang pegang tiket yang bisa oper
        if ($ticket->assigned_to != Auth::id()) {
            return redirect('/admin/tickets')->with('error','Bukan tiket kamu');
        }

        $oldTechnician = $ticket->assigned_to;
        $newTechnician = $request->assigned_to;

        // update tiket
        $ticket->update([
            'assigned_to' => $newTechnician,
            'status' => 'on_progress' // tetap dikerjakan
        ]);

        $oldName = User::find($oldTechnician)?->name ?? '-';
        $newName = User::find($newTechnician)?->name ?? '-';

        // simpan ke timeline
        \App\Models\TicketTimeline::create([
            'ticket_id' => $ticket->id,
            'status' => 'reassigned',
            'description' => "Tiket dioper dari {$oldName} ke {$newName}",
            'assigned_to' => $newTechnician,
        ]);

        return redirect('/admin/tickets')->with('success','Tiket berhasil dioper');
    }


    public function cancelled()
    {
        if(auth()->user()->role != 'admin'){
            abort(403);
        }

        $tickets = Ticket::where('status','cancelled')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.tickets.cancelled', compact('tickets'));
    }

    public function cancel(Request $request, $id)
    {
        if(auth()->user()->role != 'admin'){
            abort(403,'Akses ditolak');
        }

        $request->validate([
            'reason' => 'required'
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => 'cancelled',
            'resolved_at' => now()
        ]);

        // history
        TicketHistory::create([
            'ticket_id'  => $ticket->id,
            'status'     => 'cancelled',
            'keterangan' => $request->reason,
            'updated_by' => auth()->id()
        ]);

        // timeline
        TicketTimeline::create([
            'ticket_id'   => $ticket->id,
            'status'      => 'cancelled',
            'description' => 'Ticket dibatalkan. Alasan: '.$request->reason,
            'user_id'     => auth()->id()
        ]);

        return redirect('/admin/tickets')
            ->with(
                'success',
                'Ticket berhasil dibatalkan'
            );
    }

    public function mergeTicket(Request $request, $id)
    {
        $request->validate([
            'target_ticket_id' => 'required|exists:tickets,id',
            'merge_reason' => 'required'
        ]);

        $ticket = Ticket::findOrFail($id);

        $targetTicket = Ticket::findOrFail(
            $request->target_ticket_id
        );

        // tidak boleh merge diri sendiri
        if($ticket->id == $targetTicket->id){

            return back()->with(
                'error',
                'Ticket tidak bisa merge ke dirinya sendiri'
            );
        }

        // update ticket
        $ticket->update([

            'status' => 'merged',

            'merged_to' => $targetTicket->id,

            'merge_reason' => $request->merge_reason,

            'resolved_at' => now()
        ]);

        // timeline merge
        TicketTimeline::create([

            'ticket_id' => $ticket->id,

            'status' => 'merged',

            'description' =>
                'Ticket di merge ke #'
                .$targetTicket->ticket_code.
                ' | alasan: '
                .$request->merge_reason,

            'user_id' => auth()->id()
        ]);

        return redirect('/admin/tickets')
            ->with(
                'success',
                'Ticket berhasil di merge'
            );
    }

    public function searchTicket(Request $request)
    {
        $keyword = $request->keyword;

        $tickets = Ticket::where(
                'ticket_code',
                'LIKE',
                "%{$keyword}%"
            )
            ->where('status', '!=', 'merged')
            ->limit(10)
            ->get();

        return response()->json($tickets);
    }

    public function downloadVncConfig($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ip = trim($ticket->ip_address ?? '');

        if (empty($ip) || $ip === '-' || $ip === '127.0.0.1') {
            return redirect()->back()->with('error', 'IP Address pengguna tidak valid untuk remote TightVNC');
        }

        $vncContent = "[Connection]\r\n"
            . "Host={$ip}\r\n"
            . "Port=5900\r\n"
            . "[Options]\r\n"
            . "UseLocalCursor=1\r\n"
            . "UseDesktopResize=1\r\n"
            . "FullScreen=0\r\n";

        $cleanLaptop = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ticket->nomor_laptop ?? 'LAPTOP');
        $fileName = "remote_{$cleanLaptop}_{$ip}.vnc";

        return response($vncContent, 200, [
            'Content-Type'        => 'application/x-vnc',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    public function launchVnc($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ip = trim($ticket->ip_address ?? '');

        if (empty($ip) || $ip === '-' || $ip === '127.0.0.1') {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IP Address pengguna tidak valid untuk remote TightVNC.'
                ], 422);
            }
            return redirect()->back()->with('error', 'IP Address pengguna tidak valid untuk remote TightVNC.');
        }

        $tvnPath = 'C:\\Program Files\\TightVNC\\tvnviewer.exe';
        if (!file_exists($tvnPath)) {
            $tvnPath = 'C:\\Program Files (x86)\\TightVNC\\tvnviewer.exe';
        }

        $launched = false;
        if (file_exists($tvnPath)) {
            try {
                $cmd = 'start "" "' . $tvnPath . '" -host=' . escapeshellarg($ip);
                pclose(popen($cmd, 'r'));
                $launched = true;
            } catch (\Throwable $e) {
                $launched = false;
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'      => true,
                'launched'     => $launched,
                'ip'           => $ip,
                'vnc_url'      => 'vnc://' . $ip,
                'download_url' => url('/admin/ticket/' . $ticket->id . '/vnc'),
                'message'      => $launched 
                    ? "Membuka TightVNC ke {$ip}..." 
                    : "Mengarahkan ke TightVNC...",
            ]);
        }

        return redirect()->back()->with('success', "Perintah remote TightVNC ke {$ip} telah dijalankan.");
    }
}

