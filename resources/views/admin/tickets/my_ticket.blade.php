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
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Tidak ada tiket",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "←", next: "→" }
        }
    });
});
</script>
@stop