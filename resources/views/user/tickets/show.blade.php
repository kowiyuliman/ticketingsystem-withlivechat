<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket #{{ $ticket->ticket_code }} - Live Chat MPTB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-12">

    <!-- Header -->
    <header class="bg-white border-b border-sky-100 sticky top-0 z-50 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-2 sm:space-x-3">
                <a id="btn-back" href="{{ route('portal', ['tab' => 'create']) }}" class="p-2 sm:px-3 sm:py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white transition-all text-xs font-bold flex items-center gap-1.5 shadow-2xs {{ in_array($ticket->status, ['closed', 'cancelled']) ? '' : 'hidden' }}" title="Buat Tiket Baru">
                    <span>➕</span>
                    <span>Buat Tiket</span>
                </a>
                <a id="btn-back-history" href="{{ route('portal', ['tab' => 'history']) }}" class="p-2 sm:px-3 sm:py-1.5 rounded-xl text-slate-600 hover:text-sky-700 hover:bg-sky-50 transition-all text-xs font-bold flex items-center gap-1 border border-slate-200 {{ in_array($ticket->status, ['closed', 'cancelled']) ? '' : 'hidden' }}" title="Kembali ke Daftar Tiket">
                    <span>📋</span>
                    <span class="hidden sm:inline">Tiket Saya</span>
                </a>
                <div>
                    <h1 class="font-bold text-base text-slate-900 tracking-tight leading-none">Tiket #<span id="header-ticket-code">{{ $ticket->ticket_code }}</span></h1>
                    <p class="text-xs text-sky-600 font-medium">Laptop {{ $ticket->nomor_laptop ?? $ticket->nama }}</p>
                </div>
            </div>
            <span id="ticket-status-badge" class="text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider
                @if($ticket->status == 'open') bg-rose-100 text-rose-800
                @elseif($ticket->status == 'on_progress') bg-amber-100 text-amber-800
                @elseif($ticket->status == 'pending') bg-yellow-100 text-yellow-800
                @elseif($ticket->status == 'closed') bg-emerald-100 text-emerald-800
                @else bg-slate-100 text-slate-700 @endif">
                {{ str_replace('_', ' ', $ticket->status) }}
            </span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 pt-6">

        @include('partials.floating_toast')

        <!-- Ticket Detail Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-sm border border-sky-100 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span id="ticket-kategori-badge" class="bg-sky-100 text-sky-800 text-xs font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">
                            @if($ticket->kategori == 'software') 💻 Software
                            @elseif($ticket->kategori == 'hardware') 🔌 Hardware
                            @elseif($ticket->kategori == 'network') 🌐 Network
                            @else ❓ Other
                            @endif
                        </span>
                        <span class="text-xs text-slate-400">• {{ $ticket->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <h2 class="font-extrabold text-base sm:text-lg text-slate-900 leading-snug mb-3">{{ $ticket->deskripsi }}</h2>
                    
                    <div class="flex flex-wrap gap-2 text-xs text-slate-600">
                        <span class="bg-slate-100 px-3 py-1 rounded-xl font-medium">💻 {{ $ticket->nomor_laptop ?? '-' }}</span>
                        <span class="bg-slate-100 px-3 py-1 rounded-xl font-medium">👤 {{ $ticket->nama }}</span>
                        <span class="bg-slate-100 px-3 py-1 rounded-xl font-medium">🌐 {{ $ticket->ip_address ?? '-' }}</span>
                    </div>

                    <div id="status-reason-container" class="{{ (($ticket->status == 'pending' || $ticket->status == 'cancelled') && ($ticket->status_reason ?? $ticket->reason_text)) ? '' : 'hidden' }} mt-3 p-3 rounded-2xl {{ $ticket->status == 'pending' ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-slate-100 border border-slate-200 text-slate-800' }} text-xs font-medium">
                        <div class="font-bold uppercase tracking-wider mb-1 flex items-center gap-1.5 text-[11px]">
                            <span id="reason-icon">{{ $ticket->status == 'pending' ? '🟡' : '⚪' }}</span>
                            <span id="reason-title">Keterangan Status {{ strtoupper($ticket->status) }}:</span>
                        </div>
                        <div id="reason-text" class="leading-relaxed">{{ $ticket->status_reason ?? $ticket->reason_text }}</div>
                    </div>
                </div>

                <!-- Assigned Technician Card -->
                <div class="bg-sky-50/70 p-3.5 rounded-2xl border border-sky-100 flex items-center space-x-3 shrink-0">
                    <div class="w-10 h-10 rounded-2xl bg-sky-600 text-white font-black flex items-center justify-center text-xs shadow-xs">
                        IT
                    </div>
                    <div>
                        <span class="text-[10px] text-sky-700 font-semibold uppercase tracking-wider block">Mitra Pelita Terangi Bangsa</span>
                        <span id="tech-name" class="text-xs font-extrabold text-slate-900">{{ $ticket->technician?->name ?? ($adminName ?? 'Tim IT Support') }}</span>
                    </div>
                </div>
            </div>

            @if($ticket->screenshot)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-500 block mb-2">Lampiran Foto Tiket:</span>
                    <button type="button" onclick="openImageModal('{{ asset('storage/' . $ticket->screenshot) }}')" class="block text-left">
                        <img src="{{ asset('storage/' . $ticket->screenshot) }}" class="max-h-48 rounded-xl border border-slate-200 shadow-2xs hover:opacity-90 hover:scale-[1.01] transition-all cursor-pointer">
                    </button>
                </div>
            @endif
        </div>

        <!-- Live Chat Container -->
        <div class="bg-white rounded-3xl shadow-lg shadow-sky-100/60 border border-sky-100 overflow-hidden flex flex-col h-[560px]">
            
            <!-- Chat Header Bar -->
            <div class="bg-sky-50/80 border-b border-sky-100 px-5 py-3.5 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div id="admin-status-indicator" class="relative flex h-3.5 w-3.5 items-center justify-center">
                        @if($isAdminActive ?? false)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-extrabold text-sky-950">Live Chat Helpdesk</h3>
                            <span id="admin-presence-badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($isAdminActive ?? false) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full {{ ($isAdminActive ?? false) ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                <span id="admin-presence-text">{{ ($isAdminActive ?? false) ? (($adminName ?? $ticket->technician?->name ?? 'Admin IT') . ' Terhubung') : (($adminLastSeen ?? null) ? 'Aktif ' . \Carbon\Carbon::parse($adminLastSeen)->format('H:i') : 'Admin Belum Terhubung') }}</span>
                            </span>
                        </div>
                        <p id="admin-presence-subtext" class="text-[11px] text-sky-700 font-medium">
                            @if($isAdminActive ?? false)
                                Terhubung langsung dengan <span id="header-tech-name">{{ $adminName ?? $ticket->technician?->name ?? 'Tim IT Support' }}</span>
                            @else
                                Menunggu admin / teknisi membuka chat ini...
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Notification Sound Test Button (Hidden) -->
                    <button type="button" onclick="playNotificationSound()" title="Tes Nada Dering Notifikasi" class="hidden bg-white hover:bg-sky-100 text-sky-700 border border-sky-200 px-2.5 py-1 rounded-xl text-xs font-semibold transition-all items-center gap-1 shadow-2xs">
                        <span>🔔</span>
                        <span class="hidden sm:inline">Tes Notif</span>
                    </button>
                </div>
            </div>

            <!-- Messages Stream Area -->
            <div class="flex-1 p-5 overflow-y-auto space-y-4 bg-slate-50/40 overflow-x-hidden" id="chat-stream">
                
                <!-- Ticket Creation System Bubble -->
                <div class="text-center my-2">
                    <span class="bg-sky-100/90 text-sky-900 text-[11px] font-semibold px-3.5 py-1.5 rounded-full inline-block border border-sky-200 shadow-2xs">
                        Tiket #{{ $ticket->ticket_code }} berhasil dibuat oleh {{ $ticket->nama }} ({{ $ticket->nomor_laptop }})
                    </span>
                </div>

                @forelse($ticket->comments as $comment)
                    @php 
                        $isAdmin = (bool)$comment->is_admin;
                        $isUser = !$isAdmin;
                        $senderName = $isAdmin ? ($comment->user?->name ?? 'Admin IT') : ($ticket->nama ?: 'Pengguna');
                    @endphp
                    <div class="flex items-start {{ $isUser ? 'justify-end' : 'justify-start' }} space-x-2" data-comment-id="{{ $comment->id }}">
                        @if($isUser)
                            <div class="max-w-xs sm:max-w-md">
                                <div class="p-3.5 rounded-2xl text-xs sm:text-sm shadow-2xs bg-white border border-sky-100 text-slate-800 rounded-tr-none">
                                    @if($comment->comment)
                                        <div>{{ $comment->comment }}</div>
                                    @endif

                                    @if($comment->attachment)
                                        <div class="{{ $comment->comment ? 'mt-2 pt-2 border-t border-sky-100/40' : '' }}">
                                            <button type="button" onclick="openImageModal('{{ asset('storage/' . $comment->attachment) }}')" class="block text-left group relative max-w-[220px]">
                                                <img src="{{ asset('storage/' . $comment->attachment) }}" class="max-h-40 max-w-[220px] w-full object-cover rounded-xl border border-sky-200 group-hover:opacity-90 transition-all shadow-2xs cursor-pointer">
                                                <span class="absolute bottom-2 right-2 bg-slate-900/70 text-white text-[9px] font-bold px-2 py-0.5 rounded-lg backdrop-blur-xs flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    🔍 Klik Preview
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-end font-medium mr-1 gap-1">
                                    <span>{{ $senderName }} • {{ $comment->created_at->format('H:i') }}</span>
                                    <span class="status-tick" data-tick-id="{{ $comment->id }}">
                                        @if($comment->read_status === 'read')
                                            <span class="text-sky-600 font-extrabold tracking-[-3px]" title="Sudah Dibaca Teknisi">✓✓</span>
                                        @elseif($comment->read_status === 'delivered')
                                            <span class="text-slate-400 font-extrabold tracking-[-3px]" title="Tersampaikan ke Perangkat Teknisi">✓✓</span>
                                        @else
                                            <span class="text-slate-400 font-extrabold" title="Terkirim ke Server">✓</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-sky-200 shadow-2xs leading-none text-center">
                                👤
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-2xs leading-none text-center">
                                IT
                            </div>
                            <div class="max-w-xs sm:max-w-md">
                                <div class="p-3.5 rounded-2xl text-xs sm:text-sm shadow-2xs bg-sky-600 text-white rounded-tl-none font-medium">
                                    @if($comment->comment)
                                        <div>{{ $comment->comment }}</div>
                                    @endif

                                    @if($comment->attachment)
                                        <div class="{{ $comment->comment ? 'mt-2 pt-2 border-t border-sky-100/40' : '' }}">
                                            <button type="button" onclick="openImageModal('{{ asset('storage/' . $comment->attachment) }}')" class="block text-left group relative max-w-[220px]">
                                                <img src="{{ asset('storage/' . $comment->attachment) }}" class="max-h-40 max-w-[220px] w-full object-cover rounded-xl border border-sky-200 group-hover:opacity-90 transition-all shadow-2xs cursor-pointer">
                                                <span class="absolute bottom-2 right-2 bg-slate-900/70 text-white text-[9px] font-bold px-2 py-0.5 rounded-lg backdrop-blur-xs flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    🔍 Klik Preview
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block font-medium ml-1">
                                    {{ $senderName }} • {{ $comment->created_at->format('H:i') }}
                                </span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-xs text-slate-400 py-8 italic" id="empty-chat-msg">
                        Belum ada balasan tambahan. Tuliskan pesan atau paste (Ctrl+V) foto screenshot untuk berdiskusi dengan Tim IT.
                    </div>
                @endforelse
            </div>

            <!-- Image Preview Bar (Hidden by default) -->
            <div id="image-preview-container" class="hidden px-5 py-2.5 bg-sky-50 border-t border-sky-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <img id="image-preview-thumb" src="" class="h-12 w-12 object-cover rounded-xl border border-sky-200 shadow-2xs">
                    <div>
                        <span class="text-xs font-bold text-sky-900 block">Foto siap dikirim</span>
                        <span class="text-[10px] text-sky-600">Terkompresi otomatis & siap dikirim</span>
                    </div>
                </div>
                <button type="button" onclick="clearAttachedImage()" class="text-rose-600 hover:text-rose-700 text-xs font-bold px-2.5 py-1 rounded-lg hover:bg-rose-50 transition-all">
                    ❌ Hapus Foto
                </button>
            </div>

            <!-- Closed Ticket Notification Banner -->
            <div id="closed-banner" class="p-4 bg-amber-50/90 border-t border-amber-200 text-center flex flex-col items-center justify-center gap-2 {{ in_array($ticket->status, ['closed', 'cancelled']) ? '' : 'hidden' }}">
                <div class="flex items-center space-x-2 text-amber-900 font-bold text-xs sm:text-sm">
                    <span>🔒</span>
                    <span>Tiket ini sudah ditutup. Silakan buat tiket lagi jika Anda masih memiliki kendala.</span>
                </div>
                <div class="flex items-center gap-2 mt-1">
                    <a href="{{ route('portal', ['tab' => 'create']) }}" class="inline-flex items-center gap-1.5 bg-sky-600 hover:bg-sky-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-2xs transition-all">
                        <span>➕</span>
                        <span>Buat Tiket Baru</span>
                    </a>
                    <a href="{{ route('portal', ['tab' => 'history']) }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-xl transition-all border border-slate-200">
                        <span>📋</span>
                        <span>Tiket Saya</span>
                    </a>
                </div>
            </div>

            <!-- Chat Input Form -->
            @if(!in_array($ticket->status, ['closed', 'cancelled']))
                <div id="chat-form-container" class="p-3.5 bg-white border-t border-sky-100">
                    <form id="chat-form" action="{{ route('ticket.comment', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-1">
                        @csrf
                        <input type="hidden" name="attachment_base64" id="attachment_base64">
                        <input type="file" id="attachment_file" name="attachment" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                        
                        <div class="flex items-center space-x-2">
                            <!-- Image Attachment Button -->
                            <button type="button" onclick="document.getElementById('attachment_file').click()" title="Lampirkan Foto / Screenshot" class="p-2.5 rounded-xl border border-sky-200 text-sky-700 hover:bg-sky-50 transition-all text-sm font-semibold flex items-center justify-center">
                                📷
                            </button>

                            <input type="text" id="comment-input" name="comment" autocomplete="off" class="flex-1 bg-slate-50 border border-sky-200 rounded-xl px-4 py-2.5 text-xs sm:text-sm focus:outline-none focus:border-sky-500 focus:bg-white transition-all placeholder:text-slate-400" placeholder="Ketik pesan atau paste (Ctrl+V) screenshot...">

                            <button type="submit" id="btn-submit-comment" class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-1.5">
                                <span>Kirim</span>
                            </button>
                        </div>
                        <div class="text-[10px] text-slate-400 pl-1">
                            💡 <span class="font-medium text-sky-700">Tips:</span> Anda bisa langsung tekan <kbd class="px-1 py-0.5 bg-slate-100 border rounded text-[9px] font-bold text-slate-700">Ctrl + V</kbd> di kolom ini untuk menempelkan foto screenshot Snipping Tool.
                        </div>
                    </form>
                </div>
            @endif

        </div>

    </main>

    <!-- Image Lightbox Preview Modal -->
    <div id="image-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all" onclick="closeImageModal(event)">
        <div class="relative max-w-4xl w-full bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-slate-700/60 flex flex-col" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-slate-800/90 border-b border-slate-700/60 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-bold text-slate-200">Preview Image</span>
                </div>
                <div class="flex items-center space-x-2">
                    <a id="btn-download-img" href="" download target="_blank" class="bg-sky-600 hover:bg-sky-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1 shadow-xs">
                        <span></span>
                        <span>Unduh Image</span>
                    </a>
                    <button type="button" onclick="closeImageModal()" class="p-1.5 px-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white transition-all text-xs font-bold flex items-center gap-1">
                        <span>✖️</span>
                        <span>Tutup</span>
                    </button>
                </div>
            </div>
            <!-- Modal Image Container -->
            <div class="p-4 sm:p-6 flex items-center justify-center bg-black/50 min-h-[300px] max-h-[80vh] overflow-auto">
                <img id="modal-img-src" src="" class="max-h-[75vh] max-w-full rounded-2xl object-contain shadow-md">
            </div>
        </div>
    </div>

    <script>
        const ticketId = "{{ $ticket->id }}";
        const ticketCode = "{{ $ticket->ticket_code }}";
        const userTicketId = "{{ $ticket->user_id }}";
        const chatStream = document.getElementById('chat-stream');
        const commentInput = document.getElementById('comment-input');
        const previewContainer = document.getElementById('image-preview-container');
        const previewThumb = document.getElementById('image-preview-thumb');
        const hiddenBase64Input = document.getElementById('attachment_base64');
        const attachmentFileInput = document.getElementById('attachment_file');
        let knownCommentIds = new Set();

        // Track initial comment IDs
        document.querySelectorAll('[data-comment-id]').forEach(el => {
            knownCommentIds.add(parseInt(el.getAttribute('data-comment-id')));
        });

        // Image Lightbox Modal Handlers
        function openImageModal(imgUrl) {
            const modal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img-src');
            const downloadBtn = document.getElementById('btn-download-img');

            modalImg.src = imgUrl;
            downloadBtn.href = imgUrl;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal(e) {
            if (!e || e.target.id === 'image-modal' || e.target.closest('button')) {
                const modal = document.getElementById('image-modal');
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });

        // Listen for Clipboard Paste Event (Ctrl + V)
        commentInput.addEventListener('paste', function (e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    const blob = item.getAsFile();
                    compressAndPreviewImage(blob);
                    e.preventDefault();
                    break;
                }
            }
        });

        // Handle File Select Input
        function handleFileSelect(e) {
            const files = e.target.files;
            if (files && files[0]) {
                compressAndPreviewImage(files[0]);
            }
        }

        // Client-side Image Compression (Canvas max width 1280px, WebP quality 0.82)
        function compressAndPreviewImage(fileOrBlob) {
            const reader = new FileReader();
            reader.onload = function (event) {
                const img = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1280;
                    let width = img.width;
                    let height = img.height;

                    if (width > MAX_WIDTH) {
                        height = Math.round((height * MAX_WIDTH) / width);
                        width = MAX_WIDTH;
                    }

                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.82);
                    
                    hiddenBase64Input.value = compressedBase64;
                    previewThumb.src = compressedBase64;
                    previewContainer.classList.remove('hidden');
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(fileOrBlob);
        }

        function clearAttachedImage() {
            hiddenBase64Input.value = '';
            attachmentFileInput.value = '';
            previewThumb.src = '';
            previewContainer.classList.add('hidden');
        }

        // Synthetic Audio Chime Notification using Web Audio API
        function playNotificationSound() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                const ctx = new AudioContext();

                if (ctx.state === 'suspended') {
                    ctx.resume();
                }

                const playTone = (freq, start, duration) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
                    
                    gain.gain.setValueAtTime(0.18, ctx.currentTime + start);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + duration);
                    
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    
                    osc.start(ctx.currentTime + start);
                    osc.stop(ctx.currentTime + start + duration);
                };

                // Dual-tone high pitch chime (C5 -> E5 -> G5)
                playTone(523.25, 0, 0.12);
                playTone(659.25, 0.10, 0.12);
                playTone(783.99, 0.20, 0.30);
            } catch (e) {
                console.log('Audio notification fallback silent:', e);
            }
        }

        // Auto-scroll chat to bottom
        function scrollToBottom() {
            if (chatStream) {
                chatStream.scrollTop = chatStream.scrollHeight;
            }
        }

        // Real-time status, reason & back button sync
        function updateTicketRealtimeStatus(data) {
            if (!data.status) return;

            // 1. Update Status Badge (Text & Colors)
            const badge = document.getElementById('ticket-status-badge');
            if (badge) {
                badge.textContent = (data.status_label || data.status).toUpperCase();
                badge.className = 'text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider ';
                if (data.status === 'open') {
                    badge.className += 'bg-rose-100 text-rose-800';
                } else if (data.status === 'on_progress') {
                    badge.className += 'bg-amber-100 text-amber-800';
                } else if (data.status === 'pending') {
                    badge.className += 'bg-yellow-100 text-yellow-800';
                } else if (data.status === 'closed') {
                    badge.className += 'bg-emerald-100 text-emerald-800';
                } else {
                    badge.className += 'bg-slate-100 text-slate-700';
                }
            }

            // Update Category Badge in Real-time
            if (data.kategori) {
                const katBadge = document.getElementById('ticket-kategori-badge');
                if (katBadge) {
                    let katIcon = '❓';
                    let katName = 'Other';
                    if (data.kategori === 'software') {
                        katIcon = '💻';
                        katName = 'Software';
                    } else if (data.kategori === 'hardware') {
                        katIcon = '🔌';
                        katName = 'Hardware';
                    } else if (data.kategori === 'network') {
                        katIcon = '🌐';
                        katName = 'Network';
                    }
                    katBadge.textContent = `${katIcon} ${katName}`;
                }
            }

            // Update Ticket Code in Real-time (e.g. if category changed)
            if (data.ticket_code) {
                const headerCodeEl = document.getElementById('header-ticket-code');
                if (headerCodeEl && headerCodeEl.textContent !== data.ticket_code) {
                    headerCodeEl.textContent = data.ticket_code;
                    document.title = `Tiket #${data.ticket_code} - Live Chat MPTB`;
                }
            }

            // 2. Update Back Buttons & Closed Banner
            const backBtn = document.getElementById('btn-back');
            const backBtnHistory = document.getElementById('btn-back-history');
            const closedBanner = document.getElementById('closed-banner');
            const chatFormContainer = document.getElementById('chat-form-container');

            if (data.is_closed || data.status === 'closed' || data.status === 'cancelled') {
                if (backBtn) backBtn.classList.remove('hidden');
                if (backBtnHistory) backBtnHistory.classList.remove('hidden');
                if (closedBanner) closedBanner.classList.remove('hidden');
                if (chatFormContainer) chatFormContainer.classList.add('hidden');
            } else {
                if (backBtn) backBtn.classList.add('hidden');
                if (backBtnHistory) backBtnHistory.classList.add('hidden');
                if (closedBanner) closedBanner.classList.add('hidden');
                if (chatFormContainer) chatFormContainer.classList.remove('hidden');
            }

            // 3. Update Technician Name & Presence Status
            if (data.technician_name) {
                const techNameEl = document.getElementById('tech-name');
                if (techNameEl) techNameEl.textContent = data.technician_name;
                const headerTechName = document.getElementById('header-tech-name');
                if (headerTechName) headerTechName.textContent = data.technician_name;
            }

            const indicator = document.getElementById('admin-status-indicator');
            const presenceBadge = document.getElementById('admin-presence-badge');
            const presenceText = document.getElementById('admin-presence-text');
            const presenceSubtext = document.getElementById('admin-presence-subtext');

            const activeAdminName = data.technician_name || 'Admin IT';

            if (data.is_admin_active) {
                if (indicator) {
                    indicator.innerHTML = `
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    `;
                }
                if (presenceBadge) {
                    presenceBadge.className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1";
                }
                if (presenceText) presenceText.textContent = `${activeAdminName} Terhubung`;
                if (presenceSubtext) presenceSubtext.innerHTML = `Terhubung langsung dengan <span id="header-tech-name">${activeAdminName}</span>`;
            } else {
                if (indicator) {
                    indicator.innerHTML = `
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                    `;
                }
                if (presenceBadge) {
                    presenceBadge.className = "text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 flex items-center gap-1";
                }
                if (presenceText) {
                    presenceText.textContent = data.admin_last_seen_text ? `Aktif ${data.admin_last_seen_text}` : "Admin Belum Terhubung";
                }
                if (presenceSubtext) presenceSubtext.textContent = `Menunggu admin / teknisi membuka chat ini...`;
            }

            // 4. Update Status Reason Box if Pending / Cancelled
            const reasonContainer = document.getElementById('status-reason-container');
            if (reasonContainer) {
                if ((data.status === 'pending' || data.status === 'cancelled') && data.status_reason) {
                    reasonContainer.classList.remove('hidden');
                    const reasonIcon = document.getElementById('reason-icon');
                    const reasonTitle = document.getElementById('reason-title');
                    const reasonText = document.getElementById('reason-text');
                    if (reasonIcon) reasonIcon.textContent = data.status === 'pending' ? '🟡' : '⚪';
                    if (reasonTitle) reasonTitle.textContent = `Keterangan Status ${data.status.toUpperCase()}:`;
                    if (reasonText) reasonText.textContent = data.status_reason;
                } else if (data.status !== 'pending' && data.status !== 'cancelled') {
                    reasonContainer.classList.add('hidden');
                }
            }
        }

        // Helper to append a single comment bubble
        function appendCommentBubble(c) {
            if (knownCommentIds.has(c.id)) return;
            knownCommentIds.add(c.id);

            const emptyMsg = document.getElementById('empty-chat-msg');
            if (emptyMsg) emptyMsg.remove();

            const div = document.createElement('div');
            div.className = `flex items-start ${c.is_user ? 'justify-end' : 'justify-start'} space-x-2`;
            div.setAttribute('data-comment-id', c.id);

            let textHtml = c.comment ? `<div>${escapeHtml(c.comment)}</div>` : '';
            let imgHtml = c.attachment_url ? `
                 <div class="${c.comment ? 'mt-2 pt-2 border-t border-sky-100/40' : ''}">
                     <button type="button" onclick="openImageModal('${c.attachment_url}')" class="block text-left group relative max-w-[220px]">
                         <img src="${c.attachment_url}" class="max-h-40 max-w-[220px] w-full object-cover rounded-xl border border-sky-200 group-hover:opacity-90 transition-all shadow-2xs cursor-pointer">
                         <span class="absolute bottom-2 right-2 bg-slate-900/70 text-white text-[9px] font-bold px-2 py-0.5 rounded-lg backdrop-blur-xs flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                             🔍 Klik Preview
                         </span>
                     </button>
                 </div>
            ` : '';

            if (c.is_user) {
                let tickMark = `<span class="text-slate-400 font-extrabold" title="Terkirim ke Server">✓</span>`;
                if (c.read_status === 'read') {
                    tickMark = `<span class="text-sky-600 font-extrabold tracking-[-3px]" title="Sudah Dibaca Teknisi">✓✓</span>`;
                } else if (c.read_status === 'delivered') {
                    tickMark = `<span class="text-slate-400 font-extrabold tracking-[-3px]" title="Tersampaikan ke Perangkat Teknisi">✓✓</span>`;
                }

                div.innerHTML = `
                    <div class="max-w-xs sm:max-w-md">
                        <div class="p-3.5 rounded-2xl text-xs sm:text-sm shadow-2xs bg-white border border-sky-100 text-slate-800 rounded-tr-none">${textHtml}${imgHtml}</div>
                        <div class="text-[10px] text-slate-400 mt-1 flex items-center justify-end font-medium mr-1 gap-1">
                            <span>${escapeHtml(c.user_name)} • ${c.created_at}</span>
                            <span class="status-tick" data-tick-id="${c.id}">${tickMark}</span>
                        </div>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 font-bold text-xs flex items-center justify-center flex-shrink-0 border border-sky-200 shadow-2xs leading-none text-center">👤</div>
                `;
            } else {
                div.innerHTML = `
                    <div class="w-8 h-8 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-2xs leading-none text-center">IT</div>
                    <div class="max-w-xs sm:max-w-md">
                        <div class="p-3.5 rounded-2xl text-xs sm:text-sm shadow-2xs bg-sky-600 text-white rounded-tl-none font-medium">${textHtml}${imgHtml}</div>
                        <span class="text-[10px] text-slate-400 mt-1 block font-medium ml-1">${escapeHtml(c.user_name)} • ${c.created_at}</span>
                    </div>
                `;
            }

            chatStream.appendChild(div);
            scrollToBottom();
        }

        // Seamless Asynchronous Chat Submit (No page reload / No flicker)
        const chatForm = document.getElementById('chat-form');
        const btnSubmit = document.getElementById('btn-submit-comment');

        if (chatForm && btnSubmit) {
            chatForm.addEventListener('submit', async function (e) {
                e.preventDefault();

                const commentText = commentInput.value.trim();
                const base64Data = hiddenBase64Input.value;
                const fileData = attachmentFileInput.files[0];

                if (!commentText && !base64Data && !fileData) {
                    return;
                }

                if (btnSubmit.disabled) {
                    return;
                }

                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-60', 'cursor-not-allowed');
                btnSubmit.innerHTML = `<span>⏳</span>`;

                const formData = new FormData(chatForm);

                try {
                    const response = await fetch(chatForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.comment) {
                            appendCommentBubble(data.comment);
                        } else {
                            fetchNewComments();
                        }
                        commentInput.value = '';
                        clearAttachedImage();
                    } else {
                        const errData = await response.json().catch(() => null);
                        alert(errData?.message || 'Gagal mengirim pesan. Silakan coba lagi.');
                    }
                } catch (err) {
                    console.error('Error sending message:', err);
                    alert('Koneksi terganggu. Silakan periksa jaringan Anda.');
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('opacity-60', 'cursor-not-allowed');
                    btnSubmit.innerHTML = `<span>Kirim</span>`;
                    commentInput.focus();
                }
            });
        }

        // Scroll down initially
        scrollToBottom();

        // Polling function for real-time Live Chat updates
        async function fetchNewComments() {
            try {
                const response = await fetch(`/ticket/comments/${ticketId}`);
                if (!response.ok) return;

                const data = await response.json();
                
                // Real-time status badge, Presence & Back Button sync
                updateTicketRealtimeStatus(data);

                let hasNewMessage = false;

                if (data.comments && data.comments.length > 0) {
                    data.comments.forEach(c => {
                        // Real-time checkmark updates for already rendered user comments
                        if (c.is_user) {
                            const tickEl = document.querySelector(`.status-tick[data-tick-id="${c.id}"]`);
                            if (tickEl) {
                                if (c.read_status === 'read') {
                                    tickEl.innerHTML = `<span class="text-sky-600 font-extrabold tracking-[-3px]" title="Sudah Dibaca Teknisi">✓✓</span>`;
                                } else if (c.read_status === 'delivered') {
                                    tickEl.innerHTML = `<span class="text-slate-400 font-extrabold tracking-[-3px]" title="Tersampaikan ke Perangkat Teknisi">✓✓</span>`;
                                } else {
                                    tickEl.innerHTML = `<span class="text-slate-400 font-extrabold" title="Terkirim ke Server">✓</span>`;
                                }
                            }
                        }

                        if (!knownCommentIds.has(c.id)) {
                            hasNewMessage = true;
                            appendCommentBubble(c);
                        }
                    });
                }

                if (hasNewMessage) {
                    scrollToBottom();
                    playNotificationSound();
                }
            } catch (err) {
                console.error('Error fetching comments:', err);
            }
        }

        function escapeHtml(str) {
            return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // Poll every 3 seconds for snappy real-time responsiveness
        setInterval(fetchNewComments, 3000);

        // Play chime on page load if coming from form submit
        @if(session('success'))
            setTimeout(() => {
                playNotificationSound();
            }, 500);
        @endif
    </script>
</body>
</html>