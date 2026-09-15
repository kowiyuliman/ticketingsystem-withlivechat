@extends('adminlte::page')

@section('title', 'Management Admin')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-shield text-danger mr-2"></i>Management Admin</h1>
        <p class="text-muted text-sm mb-0">Kelola akun administrator untuk operasional sistem IT Helpdesk</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
            <i class="fas fa-plus-circle mr-1"></i> Tambah Admin Baru
        </a>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-users-cog mr-1 text-danger"></i> Daftar Administrator Sistem
                </h3>
                <span class="badge badge-danger px-3 py-1 font-weight-bold ml-auto">
                    Total: {{ $admins->count() }} Admin
                </span>
            </div>
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="table-admins" class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th class="text-center" style="width: 140px;">Role</th>
                                <th style="width: 180px;">Tanggal Dibuat</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admins as $admin)
                            <tr>
                                <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-danger text-white mr-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 50%; font-weight: bold;">
                                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <b>{{ $admin->name }}</b>
                                            @if($admin->id === auth()->id())
                                                <span class="badge badge-success ml-1 text-xs">Akun Anda</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-dark font-weight-bold bg-light px-2 py-1 rounded border">
                                        {{ $admin->username }}
                                    </code>
                                </td>
                                <td class="text-center">
                                    @if($admin->role === 'admin')
                                    <span class="badge badge-danger px-2.5 py-1 text-uppercase font-weight-bold">
                                        <i class="fas fa-shield-alt mr-1"></i> Admin
                                    </span>
                                    @elseif($admin->role === 'management')
                                    <span class="badge badge-dark px-2.5 py-1 text-uppercase font-weight-bold" style="background-color: #343a40;">
                                        <i class="fas fa-briefcase mr-1"></i> Management
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted text-sm">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ $admin->created_at ? $admin->created_at->format('d M Y, H:i') : '-' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-warning btn-xs font-weight-bold shadow-2xs mr-1" title="Edit Admin">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </a>

                                        @if($admin->id !== auth()->id() && $admins->count() > 1)
                                        <form action="{{ route('admin.admins.delete', $admin->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin {{ $admin->name }} ({{ $admin->username }})?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs font-weight-bold shadow-2xs" title="Hapus Admin">
                                                <i class="fas fa-trash-alt mr-1"></i> Hapus
                                            </button>
                                        </form>
                                        @else
                                        <button class="btn btn-secondary btn-xs font-weight-bold disabled" title="Tidak dapat menghapus akun sendiri atau admin terakhir" disabled>
                                            <i class="fas fa-lock mr-1"></i> Kunci
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data admin terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function() {
        $('#table-admins').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari Admin:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ admin",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                },
                emptyTable: "Tidak ada data admin"
            }
        });
    });
</script>
@stop

