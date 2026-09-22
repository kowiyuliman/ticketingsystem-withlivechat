@extends('adminlte::page')

@section('title', 'Daftar Tiket Kategori ' . $categoryName)

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark" style="font-size: 24px;">
            <a href="{{ url('admin/dashboard') }}" class="btn btn-sm btn-light border text-secondary mr-2" title="Kembali ke Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <i class="{{ $categoryIcon }} mr-1"></i> Tiket Kategori: {{ $categoryName }}
        </h1>
        <p class="text-muted text-sm mb-0">Daftar seluruh tiket laporan dengan kategori kendala <b>{{ $categoryName }}</b></p>
    </div>
    <div class="mt-2 mt-sm-0 d-flex gap-2">
        <a href="{{ url('admin/tickets') }}" class="btn btn-outline-primary btn-sm font-weight-bold shadow-2xs mr-2">
            <i class="fas fa-list mr-1"></i> Semua Tiket
        </a>
        <a href="{{ url('admin/dashboard') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-2xs">
            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
        </a>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
    .cat-pill {
        border-radius: 20px;
        padding: 6px 16px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .cat-pill:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
    .stat-card-widget {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 14px 16px;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .stat-card-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .stat-card-widget.active-filter {
        border-color: #0284c7;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
    }
    .stat-card-widget .stat-number {
        font-size: 24px;
        font-weight: 800;
        line-height: 1.1;
    }
    .stat-card-widget .stat-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #64748b;
    }
    .stat-card-widget .stat-icon {
        position: absolute;
        right: 12px;
        bottom: 8px;
        font-size: 32px;
        opacity: 0.12;
    }
    #categoryTicketsTable th {
        background-color: #f8fafc;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        vertical-align: middle;
        padding: 12px 10px;
    }
    #categoryTicketsTable td {
        vertical-align: middle !important;
        padding: 12px 10px;
        font-size: 13.5px;
    }
    .badge-status-pill {
        border-radius: 30px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* DataTables Bottom & Pagination Controls */
    .dataTables_wrapper {
        padding-top: 8px;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 16px !important;
        font-size: 0.875rem !important;
        color: #64748b !important;
        font-weight: 500 !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 14px !important;
        padding-bottom: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex-wrap: wrap !important;
        gap: 6px !important; /* Jarak renggang antar tombol */
    }

    /* Tombol Angka Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        padding: 6px 14px !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #334155 !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        cursor: pointer !important;
        margin: 0 4px !important; /* Jarak antar nomor halaman */
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 38px !important;
        text-align: center !important;
    }

    /* Jarak Khusus untuk Tombol Previous dan Next */
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous {
        margin-right: 14px !important; /* Jarak renggang sebelum nomor halaman */
        padding: 6px 16px !important;
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #1e293b !important;
        font-weight: 600 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        margin-left: 14px !important; /* Jarak renggang setelah nomor halaman */
        padding: 6px 16px !important;
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

@section('content')

@include('partials.floating_toast')

{{-- CATEGORY SWITCHER PILLS --}}
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-light shadow-2xs p-2 mb-0" style="border-radius: 12px; border: 1px solid #e2e8f0;">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted font-weight-bold mr-2 text-xs uppercase"><i class="fas fa-filter mr-1"></i> Kategori:</span>
                    <a href="{{ url('admin/tickets/category/software') }}" class="cat-pill mr-1 {{ $kategoriKey == 'software' ? 'bg-warning text-dark shadow-sm' : 'bg-light text-secondary border' }}">
                        💻 Software
                    </a>
                    <a href="{{ url('admin/tickets/category/hardware') }}" class="cat-pill mr-1 {{ $kategoriKey == 'hardware' ? 'bg-info text-white shadow-sm' : 'bg-light text-secondary border' }}">
                        🔌 Hardware
                    </a>
                    <a href="{{ url('admin/tickets/category/network') }}" class="cat-pill mr-1 {{ $kategoriKey == 'network' ? 'bg-success text-white shadow-sm' : 'bg-light text-secondary border' }}">
                        🌐 Network
                    </a>
                    <a href="{{ url('admin/tickets/category/other') }}" class="cat-pill {{ $kategoriKey == 'other' ? 'bg-secondary text-white shadow-sm' : 'bg-light text-secondary border' }}">
                        ❓ Other
                    </a>
                </div>
                <div class="text-muted text-xs font-weight-bold mt-2 mt-md-0">
                    <i class="fas fa-info-circle text-info mr-1"></i> Klik status di bawah untuk memfilter tabel
                </div>
            </div>
        </div>
    </div>
</div>

{{-- STATISTIK KATEGORI RINGKAS (INTERAKTIF FILTER) --}}
<div class="row mb-3">
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget active-filter" onclick="filterByStatus('')" id="filter-card-all">
            <div class="stat-number text-dark">{{ $stats['total'] }}</div>
            <div class="stat-label">Semua Tiket</div>
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget" onclick="filterByStatus('open')" id="filter-card-open">
            <div class="stat-number text-danger">{{ $stats['open'] }}</div>
            <div class="stat-label">Open</div>
            <div class="stat-icon text-danger"><i class="fas fa-folder-open"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget" onclick="filterByStatus('on_progress')" id="filter-card-on_progress">
            <div class="stat-number text-primary">{{ $stats['progress'] }}</div>
            <div class="stat-label">On Progress</div>
            <div class="stat-icon text-primary"><i class="fas fa-spinner"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget" onclick="filterByStatus('pending')" id="filter-card-pending">
            <div class="stat-number text-warning">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
            <div class="stat-icon text-warning"><i class="fas fa-pause-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget" onclick="filterByStatus('closed')" id="filter-card-closed">
            <div class="stat-number text-success">{{ $stats['closed'] }}</div>
            <div class="stat-label">Closed</div>
            <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-2">
        <div class="stat-card-widget" onclick="filterByStatus('cancelled')" id="filter-card-cancelled">
            <div class="stat-number text-secondary">{{ $stats['cancelled'] }}</div>
            <div class="stat-label">Cancelled</div>
            <div class="stat-icon text-secondary"><i class="fas fa-ban"></i></div>
        </div>
    </div>
</div>

{{-- TABEL DATA TIKET KATEGORI --}}
<div class="card card-outline card-info shadow-sm" style="border-radius: 14px; overflow: hidden; border: 1px solid #e2e8f0;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h3 class="card-title font-weight-bold text-dark m-0" style="font-size: 16px;">
                <i class="fas fa-table text-info mr-1"></i> Data Tiket Kategori {{ $categoryName }}
            </h3>
            <span class="badge badge-light border text-muted font-weight-bold ml-2">Total: {{ $tickets->count() }}</span>
        </div>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" onclick="window.location.reload();" title="Segarkan Data">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive p-3">
            <table class="table table-hover table-striped mb-0" id="categoryTicketsTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center" width="4%">No</th>
                        <th width="12%">Kode Tiket</th>
                        <th width="20%">Pengguna & Perangkat</th>
                        <th>Deskripsi Kendala</th>
                        <th class="text-center" width="12%">Status</th>
                        <th width="14%">Teknisi</th>
                        <th width="13%">Waktu Dibuat</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr data-status="{{ $t->status }}">
                        <td class="text-center text-muted font-weight-bold">{{ $loop->iteration }}</td>
                        <td>
                            <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 12px; letter-spacing: 0.3px;">
                                {{ $t->ticket_code }}
                            </span>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $t->nama }}</div>
                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1 text-xs">
                                <span class="badge bg-light border text-secondary font-weight-bold mr-1">
                                    💻 {{ $t->nomor_laptop ?? '-' }}
                                </span>
                                 @if(!empty($t->ip_address) && $t->ip_address !== '-' && $t->ip_address !== '127.0.0.1')
                                     <div class="d-inline-flex align-items-center">
                                         <a href="vnc://{{ $t->ip_address }}" onclick="launchTightVNC('{{ $t->id }}', '{{ $t->ip_address }}', event, '{{ $t->nomor_laptop }}', '{{ $t->nama }}')" class="btn btn-xs btn-outline-info font-weight-bold shadow-2xs mr-1" title="Remote Desktop via TightVNC (vnc://{{ $t->ip_address }})">
                                             🌐 {{ $t->ip_address }} <i class="fas fa-desktop ml-0.5 text-2xs"></i>
                                         </a>
                                         <button type="button" onclick="copyIpAddress('{{ $t->ip_address }}', this)" class="btn btn-xs btn-light border shadow-2xs" title="Salin IP Address">
                                             <i class="fas fa-copy text-muted"></i>
                                         </button>
                                     </div>
                                @else
                                    <span class="text-muted">🌐 {{ $t->ip_address ?? '-' }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block font-weight-medium text-secondary" style="max-width: 260px;" title="{{ $t->deskripsi }}">
                                {{ $t->deskripsi }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($t->status == 'open')
                                <span class="badge-status-pill bg-danger text-white">
                                    <i class="fas fa-folder-open text-xs"></i> Open
                                </span>
                            @elseif($t->status == 'on_progress')
                                <span class="badge-status-pill bg-primary text-white">
                                    <i class="fas fa-spinner fa-spin text-xs"></i> Progress
                                </span>
                            @elseif($t->status == 'pending')
                                <span class="badge-status-pill bg-warning text-dark">
                                    <i class="fas fa-pause-circle text-xs"></i> Pending
                                </span>
                            @elseif($t->status == 'closed')
                                <span class="badge-status-pill bg-success text-white">
                                    <i class="fas fa-check-circle text-xs"></i> Closed
                                </span>
                            @else
                                <span class="badge-status-pill bg-secondary text-white">
                                    <i class="fas fa-ban text-xs"></i> Cancelled
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($t->technician)
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-shield text-info mr-1.5" style="font-size: 14px;"></i>
                                    <span class="font-weight-bold text-dark">{{ $t->technician->name }}</span>
                                </div>
                            @else
                                <span class="text-muted font-italic text-xs">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $t->created_at->format('d M Y') }}</div>
                            <div class="text-muted text-xs"><i class="far fa-clock mr-1"></i>{{ $t->created_at->format('H:i') }} WIB</div>
                        </td>
                        <td class="text-center">
                            <a href="{{ url('admin/ticket/show/' . $t->id) }}" class="btn btn-primary btn-xs font-weight-bold shadow-2xs px-2.5 py-1.5" style="border-radius: 6px;">
                                <i class="fas fa-comments mr-1"></i> Live Chat
                                @if($t->unread_comments_count > 0)
                                    <span class="badge badge-danger ml-1" title="{{ $t->unread_comments_count }} pesan baru">
                                        {{ $t->unread_comments_count }}
                                    </span>
                                @endif
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-light d-block"></i>
                            <span class="font-weight-bold">Belum ada tiket untuk kategori {{ $categoryName }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('layouts.footer')
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
    let dtTable;

    $(document).ready(function() {
        dtTable = $('#categoryTicketsTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[6, 'desc']], // Urut berdasarkan tanggal terbaru
            columnDefs: [
                { targets: [0, 4, 7], className: 'text-center' },
                { targets: [7], orderable: false }
            ],
            language: {
                search: "Cari Tiket:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ tiket",
                infoEmpty: "Menampilkan 0 tiket",
                infoFiltered: "(difilter dari _MAX_ total tiket)",
                paginate: {
                    previous: "<i class='fas fa-chevron-left mr-1'></i> Previous",
                    next: "Next <i class='fas fa-chevron-right ml-1'></i>"
                },
                emptyTable: "Belum ada tiket untuk kategori {{ $categoryName }}"
            }
        });
    });

    window.filterByStatus = function(status) {
        $('.stat-card-widget').removeClass('active-filter');
        
        if (!status) {
            $('#filter-card-all').addClass('active-filter');
            dtTable.column(4).search('').draw();
        } else {
            $('#filter-card-' + status).addClass('active-filter');
            if (status === 'open') {
                dtTable.column(4).search('Open', true, false).draw();
            } else if (status === 'on_progress') {
                dtTable.column(4).search('Progress', true, false).draw();
            } else if (status === 'pending') {
                dtTable.column(4).search('Pending', true, false).draw();
            } else if (status === 'closed') {
                dtTable.column(4).search('Closed', true, false).draw();
            } else if (status === 'cancelled') {
                dtTable.column(4).search('Cancelled', true, false).draw();
            }
        }
    };

    window.launchTightVNC = function(ticketId, ip, event, laptop, user) {
        if (event && event.preventDefault) event.preventDefault();
        if (window.openVncModal) {
            window.openVncModal(ticketId, ip, laptop, user);
        } else {
            window.location.href = 'vnc://' + ip;
        }
    };
</script>
@stop
