@extends('adminlte::page')

@section('title', 'Live Chat & Detail Tiket #' . $ticket->ticket_code)

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <a href="{{ url('admin/tickets') }}" class="text-secondary mr-2" title="Kembali ke Daftar Tiket"><i class="fas fa-arrow-left"></i></a>
            Tiket #{{ $ticket->ticket_code }}
        </h1>
        <p class="text-muted text-sm mb-0">Live Chat & Penanganan Tiket IT</p>
    </div>
    <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
        <span class="badge px-3 py-2 text-uppercase font-weight-bold shadow-xs
            @if($ticket->status == 'open') bg-danger
            @elseif($ticket->status == 'on_progress') bg-primary
            @elseif($ticket->status == 'pending') bg-warning text-dark
            @elseif($ticket->status == 'closed') bg-success
            @else bg-secondary @endif">
            Status: {{ str_replace('_', ' ', $ticket->status) }}
        </span>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

<div class="row">

    {{-- LEFT COLUMN: TICKET DETAILS & ACTION BAR --}}
    <div class="col-lg-5 col-12 mb-3">
        
        <div class="card card-outline card-info shadow-md mb-3" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header bg-white border-bottom py-2.5 px-3">
                <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 15px;">
                    <i class="fas fa-info-circle text-info mr-1"></i> Detail Tiket #{{ $ticket->ticket_code }}
                </h3>
            </div>
            <div class="card-body p-3">

                @if(($ticket->status == 'pending' || $ticket->status == 'cancelled') && $ticket->reason_text)
                    <div class="alert alert-{{ $ticket->status == 'pending' ? 'warning' : 'secondary' }} border shadow-2xs mb-3">
                        <div class="font-weight-bold text-xs uppercase mb-1">
                            <i class="fas {{ $ticket->status == 'pending' ? 'fa-pause-circle' : 'fa-ban' }} mr-1"></i>
                            Alasan Status {{ strtoupper($ticket->status) }}:
                        </div>
                        <div class="font-medium text-sm" style="white-space: pre-wrap;">{{ $ticket->reason_text }}</div>
                    </div>
                @endif
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td width="35%" class="text-muted font-weight-bold">Pengguna / User:</td>
                        <td><b>{{ $ticket->nama }}</b></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Nomor Laptop:</td>
                        <td><span class="badge bg-info px-2.5 py-1">💻 {{ $ticket->nomor_laptop ?? '-' }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">IP Address:</td>
                        <td><span class="badge bg-light border">🌐 {{ $ticket->ip_address ?? '-' }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Kategori:</td>
                        <td><span class="badge bg-warning text-dark uppercase font-weight-bold">
                            @if($ticket->kategori == 'software') 💻 Software
                            @elseif($ticket->kategori == 'hardware') 🔌 Hardware
                            @elseif($ticket->kategori == 'network') 🌐 Network
                            @else ❓ Other @endif
                        </span></td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Waktu Buat:</td>
                        <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted font-weight-bold">Dikerjakan oleh:</td>
                        <td><b>{{ $ticket->technician?->name ?? '-' }}</b></td>
                    </tr>
                </table>

                <hr class="my-3">

                <div>
                    <span class="text-muted font-weight-bold text-xs uppercase block mb-1">Deskripsi Kendala:</span>
                    <div class="p-3 bg-light rounded border text-dark font-medium" style="white-space: pre-wrap;">{{ $ticket->deskripsi }}</div>
                </div>

                @if($ticket->screenshot)
                    <div class="mt-3">
                        <span class="text-muted font-weight-bold text-xs uppercase block mb-1">Lampiran Screenshot Awal:</span>
                        <button type="button" onclick="openImageModal('{{ asset('storage/' . $ticket->screenshot) }}')" class="btn p-0 border-0 text-left">
                            <img src="{{ asset('storage/' . $ticket->screenshot) }}" class="rounded border shadow-2xs max-h-40 hover:opacity-90 transition-all cursor-pointer">
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Status Control Panel -->
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fas fa-tasks text-primary mr-1"></i> Update Status Tiket
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ url('admin/ticket/update/' . $ticket->id) }}" method="POST" id="status-update-form">
                    @csrf
                    <div class="form-group mb-2">
                        <label class="text-xs font-weight-bold text-muted">UBAH STATUS TIKET:</label>
                        <select name="status" id="status-select" class="form-control form-control-sm font-weight-bold" onchange="handleStatusSelectChange(this)">
                            <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>🔴 Open (Menunggu Teknisi)</option>
                            <option value="on_progress" {{ $ticket->status == 'on_progress' ? 'selected' : '' }}>🔵 On Progress (Sedang Dikerjakan)</option>
                            <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>🟢 Closed (Selesai)</option>
                            <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>⚪ Cancelled (Dibatalkan)</option>
                        </select>
                    </div>

                    <!-- Dynamic Keterangan Input Field for Pending / Cancelled Statuses -->
                    <div id="keterangan-field-container" class="form-group mb-2 d-none">
                        <label class="text-xs font-weight-bold text-muted">KETERANGAN / ALASAN:</label>
                        <textarea name="keterangan" id="status-keterangan-input" class="form-control form-control-sm" rows="2" placeholder="Tuliskan keterangan / alasan..."></textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="text-xs font-weight-bold text-muted">UBAH KATEGORI:</label>
                        <select name="kategori" id="kategori-select" class="form-control form-control-sm" onchange="checkAndSubmitStatusForm()">
                            <option value="software" {{ $ticket->kategori == 'software' ? 'selected' : '' }}>💻 Software</option>
                            <option value="hardware" {{ $ticket->kategori == 'hardware' ? 'selected' : '' }}>🔌 Hardware</option>
                            <option value="network" {{ $ticket->kategori == 'network' ? 'selected' : '' }}>🌐 Network</option>
                            <option value="other" {{ $ticket->kategori == 'other' ? 'selected' : '' }}>❓ Other</option>
                        </select>
                    </div>

                    <button type="submit" id="btn-save-status" class="btn btn-sm btn-primary btn-block font-weight-bold shadow-2xs mt-2 d-none">
                        <i class="fas fa-save mr-1"></i> Simpan Update Status
                    </button>
                </form>

                @if($ticket->status == 'open')
                    <form action="{{ url('admin/ticket/take/' . $ticket->id) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block font-weight-bold shadow-xs">
                            <i class="fas fa-hand-holding-medical mr-1"></i> Take Ticket Ini (Mulai Dikerjakan Saya)
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN: REAL-TIME LIVE CHAT PANEL --}}
    <div class="col-lg-7 col-12 mb-3">
        
        <div class="card card-outline card-success shadow-md d-flex flex-column" style="height: 680px; border-radius: 16px; overflow: hidden;">
            
            <!-- Clean Header Live Chat -->
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center">
                    <span class="spinner-grow spinner-grow-sm text-primary mr-2 flex-shrink-0" role="status" style="width: 10px; height: 10px;"></span>
                    <div class="d-flex align-items-baseline flex-wrap">
                        <h5 class="font-weight-bold text-dark m-0 mr-2" style="font-size: 15px; float: none; display: inline-block;">Live Chat</h5>
                        <span class="text-muted font-weight-normal" style="font-size: 12px; margin-left: 6px;">Diskusi langsung dengan <b>{{ $ticket->nama }}</b> ({{ $ticket->nomor_laptop }})</span>
                    </div>
                </div>
                <div class="ml-auto pl-2">
                    <button type="button" onclick="playNotificationSound()" class="btn btn-xs btn-outline-info font-weight-bold shadow-2xs text-nowrap">
                        <i class="fas fa-bell mr-1"></i> Tes Sound Notif
                    </button>
                </div>
            </div>

            <!-- Chat Stream Body -->
            <div class="card-body p-3 overflow-auto flex-grow-1 bg-light" id="admin-chat-stream" style="background-color: #f8fafc !important; overflow-x: hidden !important;">
                
                <div class="text-center my-2">
                    <span class="badge badge-light border text-secondary px-3 py-1 font-weight-normal" style="font-size: 11px;">
                        🎉 Tiket dibuat oleh <b>{{ $ticket->nama }}</b> ({{ $ticket->nomor_laptop }})
                    </span>
                </div>

                @forelse($ticket->comments as $comment)
                    @php 
                        $isAdmin = (bool)$comment->is_admin;
                        $isUser = !$isAdmin;
                        $senderName = $isAdmin ? ($comment->user?->name ?? 'Admin IT') : ($ticket->nama ?: 'Pengguna');
                    @endphp
                    <div class="d-flex mb-3 {{ $isUser ? 'justify-content-start' : 'justify-content-end' }}" data-comment-id="{{ $comment->id }}">
                        @if($isUser)
                            <div class="rounded-circle bg-info text-white font-weight-bold d-inline-flex align-items-center justify-content-center mr-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px; line-height: 1; padding: 0; text-align: center;">
                                👤
                            </div>

                            <div style="max-width: 75%;">
                                <div class="p-3 rounded-2 shadow-2xs text-sm bg-white border text-dark" style="border-radius: 14px;">
                                    @if($comment->comment)
                                        <div>{{ $comment->comment }}</div>
                                    @endif

                                    @if($comment->attachment)
                                        <div class="{{ $comment->comment ? 'mt-2 pt-2 border-top border-white-50' : '' }}">
                                            <button type="button" onclick="openImageModal('{{ asset('storage/' . $comment->attachment) }}')" class="btn p-0 border-0 text-left d-block overflow-hidden" style="max-width: 220px; border-radius: 12px;">
                                                <img src="{{ asset('storage/' . $comment->attachment) }}" class="rounded border shadow-2xs hover:opacity-90 transition-all cursor-pointer w-100" style="max-height: 160px; object-fit: cover; border-radius: 12px; display: block;">
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1 ml-1" style="font-size: 10px;">
                                    <b>{{ $senderName }}</b> &bull; {{ $comment->created_at->format('H:i') }}
                                </small>
                            </div>
                        @else
                            <div style="max-width: 75%;">
                                <div class="p-3 rounded-2 shadow-2xs text-sm bg-primary text-white" style="border-radius: 14px;">
                                    @if($comment->comment)
                                        <div>{{ $comment->comment }}</div>
                                    @endif

                                    @if($comment->attachment)
                                        <div class="{{ $comment->comment ? 'mt-2 pt-2 border-top border-white-50' : '' }}">
                                            <button type="button" onclick="openImageModal('{{ asset('storage/' . $comment->attachment) }}')" class="btn p-0 border-0 text-left d-block overflow-hidden" style="max-width: 220px; border-radius: 12px;">
                                                <img src="{{ asset('storage/' . $comment->attachment) }}" class="rounded border shadow-2xs hover:opacity-90 transition-all cursor-pointer w-100" style="max-height: 160px; object-fit: cover; border-radius: 12px; display: block;">
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1 text-right mr-1" style="font-size: 10px;">
                                    <b>{{ $senderName }}</b> &bull; {{ $comment->created_at->format('H:i') }}
                                    <span class="status-tick" data-tick-id="{{ $comment->id }}">
                                        @if($comment->read_status === 'read')
                                            <span class="text-primary font-weight-bold ml-1" title="Dibaca oleh Pengguna">✓✓</span>
                                        @elseif($comment->read_status === 'delivered')
                                            <span class="text-muted font-weight-bold ml-1" title="Tersampaikan">✓✓</span>
                                        @else
                                            <span class="text-muted font-weight-bold ml-1" title="Terkirim">✓</span>
                                        @endif
                                    </span>
                                </small>
                            </div>

                            <div class="rounded-circle bg-primary text-white font-weight-bold d-inline-flex align-items-center justify-content-center ml-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px; line-height: 1; padding: 0; text-align: center;">
                                IT
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-5 italic" id="empty-admin-chat">
                        Belum ada balasan tambahan. Tuliskan balasan untuk pengguna di bawah ini.
                    </div>
                @endforelse
            </div>

            <!-- Image Preview Thumbnail Bar (Fix clearing & hiding) -->
            <div id="image-preview-container" class="d-none px-3 py-2 bg-white border-top align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img id="image-preview-thumb" src="" class="rounded border" style="width: 42px; height: 42px; object-fit: cover;">
                    <div>
                        <small class="font-weight-bold text-dark d-block">Foto siap dikirim 📷</small>
                        <small class="text-muted" style="font-size: 10px;">Terkompresi otomatis & siap terlampir</small>
                    </div>
                </div>
                <button type="button" onclick="clearAttachedImage()" class="btn btn-xs btn-outline-danger font-weight-bold">
                    &times; Hapus Foto
                </button>
            </div>

            <!-- Real-Time Chat Form Input (Zero Page Refresh) -->
            <div class="card-footer bg-white border-top p-2.5">
                <form id="admin-chat-form" action="{{ url('admin/ticket/comment/' . $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="attachment_base64" id="attachment_base64">
                    <input type="file" id="attachment_file" name="attachment" accept="image/*" class="d-none" onchange="handleFileSelect(event)">

                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" onclick="document.getElementById('attachment_file').click()" class="btn btn-outline-secondary font-weight-bold" title="Lampirkan Foto / Screenshot">
                                📷
                            </button>
                        </div>
                        <input type="text" id="admin-comment-input" name="comment" autocomplete="off" class="form-control" placeholder="Ketik pesan atau paste (Ctrl+V) screenshot...">
                        <div class="input-group-append">
                            <button type="submit" id="btn-send-admin-chat" class="btn btn-primary font-weight-bold px-4">
                                Kirim 🚀
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 10px;">
                        💡 <span class="font-weight-bold text-primary">Tips:</span> Anda bisa menekan <kbd>Ctrl + V</kbd> untuk menempelkan gambar screenshot langsung ke kolom chat ini.
                    </small>
                </form>
            </div>

        </div>

    </div>

</div>

<!-- Image Lightbox Modal -->
<div id="image-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content bg-dark text-white rounded-3">
            <div class="modal-header border-secondary py-2">
                <h5 class="modal-title text-sm font-weight-bold">🔍 Preview Gambar</h5>
                <button type="button" class="close text-white" onclick="closeImageModal()" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-3 bg-black">
                <img id="modal-img-src" src="" class="img-fluid rounded max-h-75 shadow">
            </div>
            <div class="modal-footer border-secondary py-1.5 justify-content-between">
                <a id="btn-download-img" href="" download target="_blank" class="btn btn-xs btn-primary font-weight-bold">
                    <i class="fas fa-download mr-1"></i> Unduh Foto
                </a>
                <button type="button" class="btn btn-xs btn-secondary" onclick="closeImageModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    const ticketId = "{{ $ticket->id }}";
    const ticketUserNama = "{{ addslashes($ticket->nama) }}";
    const chatStream = document.getElementById('admin-chat-stream');
    const commentInput = document.getElementById('admin-comment-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewThumb = document.getElementById('image-preview-thumb');
    const hiddenBase64Input = document.getElementById('attachment_base64');
    const attachmentFileInput = document.getElementById('attachment_file');
    const adminChatForm = document.getElementById('admin-chat-form');
    let knownCommentIds = new Set();

    document.querySelectorAll('[data-comment-id]').forEach(el => {
        knownCommentIds.add(parseInt(el.getAttribute('data-comment-id')));
    });

    // Lightbox Modal
    function openImageModal(imgUrl) {
        document.getElementById('modal-img-src').src = imgUrl;
        document.getElementById('btn-download-img').href = imgUrl;
        $('#image-modal').modal('show');
    }

    function closeImageModal() {
        $('#image-modal').modal('hide');
    }

    // Ctrl + V Clipboard Paste
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

    function handleFileSelect(e) {
        const files = e.target.files;
        if (files && files[0]) {
            compressAndPreviewImage(files[0]);
        }
    }

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
                previewContainer.classList.remove('d-none');
                previewContainer.classList.add('d-flex');
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(fileOrBlob);
    }

    // Fix clear attached image functionality
    function clearAttachedImage() {
        hiddenBase64Input.value = '';
        attachmentFileInput.value = '';
        previewThumb.src = '';
        previewContainer.classList.remove('d-flex');
        previewContainer.classList.add('d-none');
    }

    // Real-Time Chat Submission (Zero Page Refresh)
    adminChatForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const commentText = commentInput.value.trim();
        const base64Data = hiddenBase64Input.value;
        const fileData = attachmentFileInput.files[0];

        if (!commentText && !base64Data && !fileData) {
            return;
        }

        const formData = new FormData(adminChatForm);

        try {
            const btnSubmit = document.getElementById('btn-send-admin-chat');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '⏳ Mengirim...';

            const response = await fetch(adminChatForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                commentInput.value = '';
                clearAttachedImage();

                if (data.comment && !knownCommentIds.has(data.comment.id)) {
                    knownCommentIds.add(data.comment.id);
                    appendCommentBubble(data.comment);
                    scrollToBottom();
                }
            } else {
                console.error('Error sending comment');
            }
        } catch (err) {
            console.error('AJAX Submit Error:', err);
        } finally {
            const btnSubmit = document.getElementById('btn-send-admin-chat');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Kirim 🚀';
        }
    });

    function appendCommentBubble(c) {
        const emptyMsg = document.getElementById('empty-admin-chat');
        if (emptyMsg) emptyMsg.remove();

        const div = document.createElement('div');
        div.className = `d-flex mb-3 ${c.is_user ? 'justify-content-start' : 'justify-content-end'}`;
        div.setAttribute('data-comment-id', c.id);

        let textHtml = c.comment ? `<div>${escapeHtml(c.comment)}</div>` : '';
        let imgHtml = c.attachment_url ? `
            <div class="${c.comment ? 'mt-2 pt-2 border-top border-white-50' : ''}">
                <button type="button" onclick="openImageModal('${c.attachment_url}')" class="btn p-0 border-0 text-left d-block overflow-hidden" style="max-width: 220px; border-radius: 12px;">
                    <img src="${c.attachment_url}" class="rounded border shadow-2xs hover:opacity-90 transition-all cursor-pointer w-100" style="max-height: 160px; object-fit: cover; border-radius: 12px; display: block;">
                </button>
            </div>
        ` : '';

        if (c.is_user) {
            div.innerHTML = `
                <div class="rounded-circle bg-info text-white font-weight-bold d-inline-flex align-items-center justify-content-center mr-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px; line-height: 1; padding: 0; text-align: center;">👤</div>
                <div style="max-width: 75%;">
                    <div class="p-3 rounded-2 shadow-2xs text-sm bg-white border text-dark" style="border-radius: 14px;">${textHtml}${imgHtml}</div>
                    <small class="text-muted d-block mt-1 ml-1" style="font-size: 10px;"><b>${escapeHtml(c.user_name)}</b> &bull; ${c.created_at}</small>
                </div>
            `;
        } else {
            let tickMark = `<span class="text-muted font-weight-bold ml-1" title="Terkirim">✓</span>`;
            if (c.read_status === 'read') {
                tickMark = `<span class="text-primary font-weight-bold ml-1" title="Dibaca oleh Pengguna">✓✓</span>`;
            } else if (c.read_status === 'delivered') {
                tickMark = `<span class="text-muted font-weight-bold ml-1" title="Tersampaikan">✓✓</span>`;
            }

            div.innerHTML = `
                <div style="max-width: 75%;">
                    <div class="p-3 rounded-2 shadow-2xs text-sm bg-primary text-white" style="border-radius: 14px;">${textHtml}${imgHtml}</div>
                    <small class="text-muted d-block mt-1 text-right mr-1" style="font-size: 10px;">
                        <b>${escapeHtml(c.user_name)}</b> &bull; ${c.created_at}
                        <span class="status-tick" data-tick-id="${c.id}">${tickMark}</span>
                    </small>
                </div>
                <div class="rounded-circle bg-primary text-white font-weight-bold d-inline-flex align-items-center justify-content-center ml-2 flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px; line-height: 1; padding: 0; text-align: center;">IT</div>
            `;
        }

        chatStream.appendChild(div);
    }

    // Synthetic Web Audio API Chime Notification
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

            playTone(523.25, 0, 0.12);
            playTone(659.25, 0.10, 0.12);
            playTone(783.99, 0.20, 0.30);
        } catch (e) {
            console.log('Audio notification fallback:', e);
        }
    }

    function scrollToBottom() {
        if (chatStream) {
            chatStream.scrollTop = chatStream.scrollHeight;
        }
    }

    scrollToBottom();

    // Polling Comments Every 3 Seconds
    async function fetchNewComments() {
        try {
            const response = await fetch(`/ticket/comments/${ticketId}`);
            if (!response.ok) return;

            const data = await response.json();
            let hasNewMessage = false;

            if (data.comments && data.comments.length > 0) {
                data.comments.forEach(c => {
                    // Update checkmarks for existing admin bubbles
                    if (!c.is_user) {
                        const tickEl = document.querySelector(`.status-tick[data-tick-id="${c.id}"]`);
                        if (tickEl) {
                            if (c.read_status === 'read') {
                                tickEl.innerHTML = `<span class="text-primary font-weight-bold ml-1" title="Dibaca oleh Pengguna">✓✓</span>`;
                            } else if (c.read_status === 'delivered') {
                                tickEl.innerHTML = `<span class="text-muted font-weight-bold ml-1" title="Tersampaikan">✓✓</span>`;
                            } else {
                                tickEl.innerHTML = `<span class="text-muted font-weight-bold ml-1" title="Terkirim">✓</span>`;
                            }
                        }
                    }

                    if (!knownCommentIds.has(c.id)) {
                        knownCommentIds.add(c.id);
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
            console.error('Error fetching admin comments:', err);
        }
    }

    function escapeHtml(str) {
        return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    setInterval(fetchNewComments, 4000);

    // Handle Status Select Dropdown Changes (Pending / Cancelled Reason Input)
    window.handleStatusSelectChange = function(selectElem) {
        const val = selectElem.value;
        const keteranganContainer = document.getElementById('keterangan-field-container');
        const keteranganInput = document.getElementById('status-keterangan-input');
        const btnSave = document.getElementById('btn-save-status');

        if (val === 'pending' || val === 'cancelled') {
            keteranganContainer.classList.remove('d-none');
            btnSave.classList.remove('d-none');
            keteranganInput.focus();
            if (val === 'pending') {
                keteranganInput.placeholder = 'Tuliskan keterangan / alasan pending (misal: Menunggu sparepart / konfirmasi user)...';
            } else {
                keteranganInput.placeholder = 'Tuliskan alasan pembatalan tiket...';
            }
        } else {
            keteranganContainer.classList.add('d-none');
            btnSave.classList.add('d-none');
            selectElem.form.submit();
        }
    };

    window.checkAndSubmitStatusForm = function() {
        const statusSelect = document.getElementById('status-select');
        if (statusSelect && statusSelect.value !== 'pending' && statusSelect.value !== 'cancelled') {
            statusSelect.form.submit();
        }
    };
</script>
@stop