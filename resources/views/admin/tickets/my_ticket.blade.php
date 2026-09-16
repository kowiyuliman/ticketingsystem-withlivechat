@extends('adminlte::page')

@section('title', 'Tiket Saya')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">Tiket Saya</h1>
        <p class="text-muted text-sm mb-0">Daftar tiket yang saat ini sedang Anda tangani</p>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

<div class="card card-outline card-info shadow-sm">
    <div class="card-header bg-light">
        <h3 class="card-title font-weight-bold text-dark">
            <i class="fas fa-user-cog text-info mr-1"></i> Tiket Dalam Penanganan Saya
        </h3>
    </div>

    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="myTicketTable" class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th>Kode Tiket</th>
                        <th>Pelapor / Laptop</th>
                        <th>Status</th>
                        <th>Kategori</th>
                        <th>Deskripsi Kendala</th>
                        <th>Waktu Mulai</th>
                        <th class="text-center">Aksi Live Chat</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                        <td>
                            <b>{{ $ticket->nama }}</b>
                            <div class="text-muted text-xs">💻 {{ $ticket->nomor_laptop }}</div>
                        </td>
                        <td>
                            @if($ticket->status == 'open')
                                <span class="badge bg-danger">OPEN</span>
                            @elseif($ticket->status == 'on_progress')
                                <span class="badge bg-primary">PROGRESS</span>
                            @elseif($ticket->status == 'pending')
                                <span class="badge bg-warning text-dark">PENDING</span>
                            @elseif($ticket->status == 'closed')
                                <span class="badge bg-success">CLOSED</span>
                            @endif
                        </td>
                        <td><span class="badge badge-warning uppercase font-weight-bold">{{ strtoupper($ticket->kategori ?? 'General') }}</span></td>
                        <td><span class="text-truncate d-inline-block" style="max-width: 220px;" title="{{ $ticket->deskripsi }}">{{ $ticket->deskripsi }}</span></td>
                        <td>{{ $ticket->started_at ? $ticket->started_at->format('d M Y, H:i') : '-' }}</td>
                        <td class="text-center">
                            <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs">
                                <i class="fas fa-comments"></i> Buka Live Chat
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper { padding-top: 6px; }
    .dataTables_wrapper .dataTables_info {
        padding-top: 14px !important;
        font-size: 0.875rem !important;
        color: #64748b !important;
        font-weight: 500 !important;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 10px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 5px 12px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #334155 !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        margin: 0 3px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous {
        margin-right: 12px !important;
        padding: 5px 14px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        margin-left: 12px !important;
        padding: 5px 14px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        opacity: 0.65;
    }
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 5px 14px;
        margin-left: 8px;
    }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#myTicketTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: "Cari Tiket:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada tiket",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tiket",
            infoEmpty: "Menampilkan 0 tiket",
            infoFiltered: "(disaring dari _MAX_ total tiket)",
            paginate: { 
                previous: "<i class='fas fa-chevron-left mr-1'></i> Previous", 
                next: "Next <i class='fas fa-chevron-right ml-1'></i>" 
            }
        }
    });
});
</script>
@stop