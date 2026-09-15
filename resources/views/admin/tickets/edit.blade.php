@extends('adminlte::page')

@section('title','Edit Ticket')

@section('content_header')
<h1>Edit Ticket</h1>
@stop

@section('content')

<!-- @if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif -->

<div class="row">

    {{-- LEFT: INFORMASI TICKET --}}
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Ticket</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" value="{{ $ticket->nama }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Nomor Meja</label>
                    <input type="text" value="{{ $ticket->nomor_meja }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Nomor Ruangan</label>
                    <input type="text" value="{{ $ticket->nomor_ruangan }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>IP Address</label>
                    <input type="text" value="{{ $ticket->ip_address }}" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea class="form-control" rows="3" readonly>{{ $ticket->deskripsi }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: ACTION IT --}}
    <div class="col-md-6">
        <form action="{{ url('/admin/ticket/update/'.$ticket->id) }}" method="POST">
        @csrf
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Update Ticket</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" class="form-control" required>
                            <option value="hardware" {{ $ticket->kategori=='hardware'?'selected':'' }}>Hardware</option>
                            <option value="software" {{ $ticket->kategori=='software'?'selected':'' }}>Software</option>
                            <option value="network" {{ $ticket->kategori=='network'?'selected':'' }}>Network</option>
                            <option value="other" {{ $ticket->kategori=='other'?'selected':'' }}>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To</label>
                        <div class="alert alert-info">
                            Sedang dikerjakan oleh: <b>{{ $ticket->technician->name ?? '-' }}</b>
                        </div>
                        <!-- <select name="assigned_to" class="form-control"
                            {{ $ticket->assigned_to != auth()->id() ? 'disabled' : '' }}>
                            <option value="1" {{ $ticket->assigned_to==1?'selected':'' }}>Bayu</option>
                            <option value="2" {{ $ticket->assigned_to==2?'selected':'' }}>Zulfan</option>
                            <option value="3" {{ $ticket->assigned_to==3?'selected':'' }}>Kowi</option>
                        </select>
                        @if($ticket->assigned_to != auth()->id())
                            <small class="text-danger">
                                🔒 Hanya pemilik tiket yang bisa mengubah assign
                            </small>
                        @endif
                        @if($ticket->assigned_to != auth()->id())
                            <input type="hidden" name="assigned_to" value="{{ $ticket->assigned_to }}">
                        @endif -->
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="statusSelect" class="form-control" required>
                            <option value="open" {{ $ticket->status=='open'?'selected':'' }}> Open </option>
                            <option value="on_progress" {{ $ticket->status=='on_progress'?'selected':'' }}> On Progress </option>
                            <option value="pending" {{ $ticket->status=='pending'?'selected':'' }}> Pending </option>
                            <option value="closed" {{ $ticket->status=='closed'?'selected':'' }}> Closed </option>
                            <option value="merged" {{ $ticket->status=='merged'?'selected':'' }}>
                                Merge Ticket
                            </option>
                        </select>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </div>
            </div>
        </form>
        <div class="card card-outline card-warning mt-3">
                <div class="card-header">
                    <h3 class="card-title">Oper Tiket</h3>
                </div>
        <div class="card-body">
            <form action="/admin/ticket/reassign/{{ $ticket->id }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Pindahkan Tiket</label>
                    <select name="assigned_to" class="form-control" required
                        {{ $ticket->assigned_to != auth()->id() ? 'disabled' : '' }}>
                        @foreach($technicians as $tech)
                            @if($tech->id != auth()->id())
                                <option value="{{ $tech->id }}">
                                    {{ $tech->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @if($ticket->assigned_to != auth()->id())
                            <small class="text-danger">
                                🔒 Hanya pemilik tiket yang bisa oper tiket
                            </small>
                        @endif
                </div>
                <button class="btn btn-warning mt-2" onclick="return confirm('Yakin oper tiket?')">
                    <i class="fas fa-random"></i> Oper Tiket
                </button>
            </form>
    </div>
</div>
    </div>
</div>

{{-- KOMENTAR --}}
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Komentar / Progress</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/ticket/comment/{{$ticket->id}}">
            @csrf
            <div class="form-group">
                <textarea name="comment" class="form-control" placeholder="Tambahkan remark pekerjaan..." required></textarea>
            </div>
            <button class="btn btn-success mt-2">
                <i class="fas fa-paper-plane"></i> Tambah Komentar
            </button>
        </form>

        <hr>

        @foreach($ticket->comments as $comment)
        <div class="card mb-2">
            <div class="card-body">
                <b>{{ $comment->user->name }}</b>
                <small class="text-muted">
                    {{ $comment->created_at->format('d-m-Y H:i') }}
                </small>
                <p class="mb-0">{{ $comment->comment }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Waktu</h3>
    </div>
    <div class="card-body">

        {{-- START --}}
        <p>
            <b>Mulai Dikerjakan:</b><br>
            @if($ticket->started_at instanceof \Carbon\Carbon)
                {{ $ticket->started_at->format('d-m-Y H:i') }}
            @else
                <span class="text-danger">Belum dimulai</span>
            @endif
        </p>

        {{-- END --}}
        <p>
            <b>Selesai:</b><br>
            @if($ticket->resolved_at)
                {{ $ticket->resolved_at->format('d-m-Y H:i') }}
            @else
                <span class="text-warning">Belum selesai</span>
            @endif
        </p>

        <hr>

        {{-- DURASI --}}
        <p>
            <b>Durasi Pengerjaan:</b><br>
            @if($ticket->started_at && $ticket->resolved_at)
                @php
                    $start = $ticket->started_at;
                    $end = $ticket->resolved_at;
                    $hours = $start->diffInHours($end);
                    $minutes = $start->diffInMinutes($end) % 60;
                @endphp
                <span class="badge bg-success">
                    {{ $minutes }} menit
                </span>

                @elseif($ticket->started_at)
                    <span class="badge bg-primary">
                        @if(!$ticket->resolved_at)
                            Sedang berjalan: 
                        @endif
                        {{ $ticket->durasi_menit }}
                    </span>

                @elseif($ticket->status == 'merged')
                    <span class="badge bg-secondary">
                        Merged
                    </span>

                @else
                    <span class="badge bg-secondary">
                        Belum ada aktivitas
                    </span>
                
            @endif
        </p>
    </div>
<br>
    <form action="{{ url('/admin/ticket/cancel/'.$ticket->id) }}"
        method="POST">
        @csrf
        <div class="form-group">
            <label>Alasan Cancel</label>
            <textarea
                name="reason"
                class="form-control"
                rows="3"
                required></textarea>
        </div>
        <button
            type="submit"
            class="btn btn-danger"
            onclick="return confirm('Yakin ingin membatalkan ticket ini?')">

            <i class="fas fa-times-circle"></i>
            Cancel Ticket
        </button>
    </form>
</div>
</div>
{{-- MODAL MERGE --}}
<div class="modal fade" id="mergeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ url('/admin/ticket/merge/'.$ticket->id) }}"
              method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        Merge Ticket
                    </h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- TICKET INDUK --}}
                    <div class="form-group">
                        <label>Nomor Ticket Induk</label>
                        <input type="text"
                               name="target_ticket_id"
                               class="form-control"
                               placeholder="Masukkan ID Ticket"
                               required>
                    </div>
                    {{-- ALASAN --}}
                    <div class="form-group">
                        <label>Alasan Merge</label>

                        <textarea name="merge_reason"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Contoh: Ticket duplicate user yang sama"
                                  required></textarea>
                    </div>

                    <div class="alert alert-info">
                        Ticket ini akan digabung ke ticket induk.
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">

                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-warning">

                        <i class="fas fa-code-branch"></i>
                        Merge Ticket
                    </button>

                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .toast-custom {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 320px;
        max-width: 420px;
        padding: 14px 18px;
        border-radius: 12px;
        color: #fff;
        z-index: 99999;
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
        animation: slideIn .3s ease;
        font-size: 14px;
    }

    .toast-content{
        display:flex;
        align-items:center;
        gap:10px;
    }

    .toast-icon{
        font-size:18px;
        font-weight:bold;
    }

    .toast-success{
        background:#28a745;
    }

    .toast-error{
        background:#dc3545;
    }

    .toast-warning{
        background:#ffc107;
        color:#000;
    }

    .toast-hide{
        animation: slideOut .5s ease forwards;
    }

    @keyframes slideIn{
        from{
            opacity:0;
            transform:translateX(100%);
        }
        to{
            opacity:1;
            transform:translateX(0);
        }
    }
    @keyframes slideOut{
        from{
            opacity:1;
            transform:translateX(0);
        }
        to{
            opacity:0;
            transform:translateX(100%);
        }
    }

    </style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function showToast(message, type = 'success') {
    let icon = '';
    if(type === 'success') icon = '✔';
    if(type === 'error') icon = '✖';
    if(type === 'warning') icon = '⚠';

    const toast = document.createElement('div');
    toast.className = 'toast-custom toast-' + type;

    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${icon}</span>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('toast-hide');
            setTimeout(() => {
                toast.remove();
            }, 500);
        }, 3000);
    }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            if (!sessionStorage.getItem('toast_success')) {
                showToast(
                    "{{ session('success') }}",
                    'success'
                );
                sessionStorage.setItem(
                    'toast_success',
                    '1'
                );
            }
        @endif
        @if(session('error'))
            if (!sessionStorage.getItem('toast_error')) {
                showToast(
                    "{{ session('error') }}",
                    'error'
                );
                sessionStorage.setItem(
                    'toast_error',
                    '1'
                );
            }
        @endif
    });

    window.addEventListener('beforeunload', function () {
        sessionStorage.removeItem('toast_success');
        sessionStorage.removeItem('toast_error');

    });
</script>


<script>
    $('#table-cancelled').DataTable({
        order: [[4, 'desc']],
        responsive: true,
        autoWidth: false,
        language: {
            emptyTable: "Tidak ada tiket cancel"
        }
    });
</script>

<script>

document.addEventListener('DOMContentLoaded', function(){

    const statusSelect =
        document.getElementById('statusSelect');

    statusSelect.addEventListener('change', function(){

        if(this.value === 'merged'){

            $('#mergeModal').modal('show');

        }

    });

});

</script>

@stop