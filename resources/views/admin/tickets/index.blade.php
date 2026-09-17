@extends('adminlte::page')

@section('title', 'Daftar Tiket Pengaduan IT')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">Daftar Tiket Pengaduan IT</h1>
        <p class="text-muted text-sm mb-0">Kelola dan tangani laporan kendala dari pengguna</p>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

@php
    $tabParam = strtolower(request('tab', 'open'));
    if (in_array($tabParam, ['on_progress', 'progress'])) {
        $activeTab = 'progress';
    } elseif (in_array($tabParam, ['pending'])) {
        $activeTab = 'pending';
    } elseif (in_array($tabParam, ['closed', 'close'])) {
        $activeTab = 'closed';
    } elseif (in_array($tabParam, ['cancel', 'cancelled'])) {
        $activeTab = 'cancel';
    } else {
        $activeTab = 'open';
    }
@endphp

<div class="card card-outline card-info shadow-sm">
    <div class="card-header p-2 bg-light">
        <ul class="nav nav-pills" id="ticketTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'open' ? 'active' : '' }} font-weight-bold" id="open-tab" data-toggle="pill" href="#open-tickets" role="tab">
                    🔴 Open <span class="badge badge-danger ml-1">{{ $tickets_open->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'progress' ? 'active' : '' }} font-weight-bold" id="progress-tab" data-toggle="pill" href="#progress-tickets" role="tab">
                    🔵 On Progress <span class="badge badge-primary ml-1">{{ $tickets_progress->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'pending' ? 'active' : '' }} font-weight-bold" id="pending-tab" data-toggle="pill" href="#pending-tickets" role="tab">
                    🟡 Pending <span class="badge badge-warning text-dark ml-1">{{ $tickets_pending->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'closed' ? 'active' : '' }} font-weight-bold" id="closed-tab" data-toggle="pill" href="#closed-tickets" role="tab">
                    🟢 Closed <span class="badge badge-success ml-1">{{ $tickets_closed->total() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'cancel' ? 'active' : '' }} font-weight-bold" id="cancel-tab" data-toggle="pill" href="#cancel-tickets" role="tab">
                    ⚪ Cancelled <span class="badge badge-secondary ml-1">{{ count($tickets_cancel) }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-3">
        <div class="tab-content" id="ticketTabsContent">
            
            <!-- OPEN TICKETS -->
            <div class="tab-pane fade {{ $activeTab == 'open' ? 'show active' : '' }}" id="open-tickets" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-open" class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Pelapor / Laptop</th>
                                <th>Kategori</th>
                                <th>Deskripsi Kendala</th>
                                <th>Waktu Buat</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_open as $ticket)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                                    <td>
                                        <b>{{ $ticket->nama }}</b>
                                        <div class="text-muted text-xs">💻 {{ $ticket->nomor_laptop }} &bull; 🌐 {{ $ticket->ip_address }}</div>
                                    </td>
                                    <td><span class="badge badge-warning uppercase font-weight-bold">{{ strtoupper($ticket->kategori ?? 'General') }}</span></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $ticket->deskripsi }}">{{ $ticket->deskripsi }}</span></td>
                                    <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                                    <td class="text-center">
                                        <form action="{{ url('/admin/ticket/take/' . $ticket->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs font-weight-bold shadow-2xs mr-1" title="Take Ticket">
                                                <i class="fas fa-hand-holding-medical"></i> Take
                                            </button>
                                        </form>
                                        <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs">
                                            <i class="fas fa-comments"></i> Live Chat
                                            @if(($ticket->unread_comments_count ?? 0) > 0)
                                                <span class="badge badge-danger ml-1" title="{{ $ticket->unread_comments_count }} pesan baru"><i class="fas fa-circle text-xs"></i> {{ $ticket->unread_comments_count }}</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ON PROGRESS TICKETS -->
            <div class="tab-pane fade {{ $activeTab == 'progress' ? 'show active' : '' }}" id="progress-tickets" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-progress" class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Pelapor / Laptop</th>
                                <th>Kategori</th>
                                <th>Deskripsi Kendala</th>
                                <th>Teknisi IT</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_progress as $ticket)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                                    <td>
                                        <b>{{ $ticket->nama }}</b>
                                        <div class="text-muted text-xs">💻 {{ $ticket->nomor_laptop }}</div>
                                    </td>
                                    <td><span class="badge badge-warning uppercase font-weight-bold">{{ strtoupper($ticket->kategori ?? 'General') }}</span></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $ticket->deskripsi }}">{{ $ticket->deskripsi }}</span></td>
                                    <td><b>{{ $ticket->technician->name ?? '-' }}</b></td>
                                    <td class="text-center">
                                        <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs">
                                            <i class="fas fa-comments"></i> Live Chat
                                            @if(($ticket->unread_comments_count ?? 0) > 0)
                                                <span class="badge badge-danger ml-1" title="{{ $ticket->unread_comments_count }} pesan baru"><i class="fas fa-circle text-xs"></i> {{ $ticket->unread_comments_count }}</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PENDING TICKETS -->
            <div class="tab-pane fade {{ $activeTab == 'pending' ? 'show active' : '' }}" id="pending-tickets" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-pending" class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Pelapor / Laptop</th>
                                <th>Teknisi IT</th>
                                <th>Deskripsi Kendala</th>
                                <th>Alasan Pending</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_pending as $ticket)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                                    <td><b>{{ $ticket->nama }}</b> ({{ $ticket->nomor_laptop }})</td>
                                    <td><b>{{ $ticket->technician->name ?? '-' }}</b></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $ticket->deskripsi }}">{{ $ticket->deskripsi }}</span></td>
                                    <td>
                                        @if($ticket->reason_text)
                                             <span class="badge badge-warning text-dark text-wrap text-left font-weight-normal p-1.5" style="max-width: 220px; font-size: 11px;">
                                                 ⚠️ {{ $ticket->reason_text }}
                                             </span>
                                        @else
                                             <span class="text-muted text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs">
                                            <i class="fas fa-comments"></i> Live Chat
                                            @if(($ticket->unread_comments_count ?? 0) > 0)
                                                <span class="badge badge-danger ml-1" title="{{ $ticket->unread_comments_count }} pesan baru"><i class="fas fa-circle text-xs"></i> {{ $ticket->unread_comments_count }}</span>
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CLOSED TICKETS -->
            <div class="tab-pane fade {{ $activeTab == 'closed' ? 'show active' : '' }}" id="closed-tickets" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-closed" class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Pelapor / Laptop</th>
                                <th>Teknisi IT</th>
                                <th>Durasi Pengerjaan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_closed as $ticket)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                                    <td><b>{{ $ticket->nama }}</b> ({{ $ticket->nomor_laptop }})</td>
                                    <td><b>{{ $ticket->technician->name ?? '-' }}</b></td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ $ticket->durasi_menit ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs">
                                            <i class="fas fa-eye"></i> Detail & Chat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CANCELLED TICKETS -->
            <div class="tab-pane fade {{ $activeTab == 'cancel' ? 'show active' : '' }}" id="cancel-tickets" role="tabpanel">
                <div class="table-responsive">
                    <table id="table-cancel" class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Tiket</th>
                                <th>Pelapor / Laptop</th>
                                <th>Deskripsi Kendala</th>
                                <th>Alasan Pembatalan</th>
                                <th>Waktu Batal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets_cancel as $ticket)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-light border font-weight-bold">{{ $ticket->ticket_code }}</span></td>
                                    <td><b>{{ $ticket->nama }}</b> ({{ $ticket->nomor_laptop }})</td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $ticket->deskripsi }}">{{ $ticket->deskripsi }}</span></td>
                                    <td>
                                        @if($ticket->reason_text)
                                             <span class="badge badge-secondary text-wrap text-left font-weight-normal p-1.5" style="max-width: 220px; font-size: 11px;">
                                                 🚫 {{ $ticket->reason_text }}
                                             </span>
                                        @else
                                             <span class="text-muted text-xs">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->updated_at->format('d M Y, H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ url('/admin/ticket/show/' . $ticket->id) }}" class="btn btn-secondary btn-xs font-weight-bold shadow-2xs">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
    /* DataTables Bottom & Top Layout */
    .dataTables_wrapper {
        padding-top: 6px;
    }

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
        flex-wrap: wrap !important;
        gap: 6px !important; /* Jarak antar tombol pagination */
    }

    /* Tombol Angka Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 5px 12px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #334155 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        cursor: pointer !important;
        margin: 0 3px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Jarak Khusus untuk Tombol Previous dan Next */
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous {
        margin-right: 12px !important; /* Jarak renggang sebelum nomor halaman */
        padding: 5px 14px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #1e293b !important;
        font-weight: 600 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        margin-left: 12px !important; /* Jarak renggang setelah nomor halaman */
        padding: 5px 14px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #1e293b !important;
        font-weight: 600 !important;
    }

    /* Hover State */
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25) !important;
        transform: translateY(-1px);
    }

    /* Active / Current Page */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3) !important;
    }

    /* Disabled State */
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 0.65;
    }

    /* Length & Search Filter Controls */
    .dataTables_wrapper .dataTables_length {
        margin-bottom: 12px;
        color: #64748b;
        font-size: 0.875rem;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        margin: 0 6px;
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 12px;
        color: #64748b;
        font-size: 0.875rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        border: 1px solid #cbd5e1;
        padding: 5px 14px;
        margin-left: 8px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function(){
        const tableConfig = {
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            language: {
                emptyTable: "Tidak ada tiket",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tiket",
                infoEmpty: "Menampilkan 0 tiket",
                infoFiltered: "(disaring dari _MAX_ total tiket)",
                lengthMenu: "Tampilkan _MENU_ tiket",
                search: "Cari Tiket:",
                paginate: {
                    previous: "<i class='fas fa-chevron-left mr-1'></i> Previous",
                    next: "Next <i class='fas fa-chevron-right ml-1'></i>"
                }
            }
        };

        $('#table-open').DataTable(tableConfig);
        $('#table-progress').DataTable(tableConfig);
        $('#table-pending').DataTable(tableConfig);
        $('#table-closed').DataTable(tableConfig);
        $('#table-cancel').DataTable(tableConfig);

        // Adjust DataTables on tab switch
        $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
        });
    });
</script>
@stop