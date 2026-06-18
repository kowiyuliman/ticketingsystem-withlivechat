@extends('adminlte::page')
@section('title', 'Laptop Loans')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Peminjaman Laptop</h1>
        <a href="{{ route('admin.inventory.loans.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Buat Peminjaman
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-info">
            <h3 class="card-title mb-0 text-white"><i class="fas fa-handshake mr-1"></i> Status Peminjaman Laptop & Jaminan KTP/SIM</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Laptop</th>
                            <th>Tanggal Pinjam</th>
                            <th>Jaminan</th>
                            <th>No Jaminan</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>{{ ($loans->currentPage() - 1) * $loans->perPage() + $loop->iteration }}</td>
                                <td class="font-weight-bold">
                                    {{ $loan->user->name }}
                                    <span class="d-block text-muted text-sm">{{ $loan->user->username }}</span>
                                </td>
                                <td>
                                    @if($loan->assignment && $loan->assignment->asset)
                                        <span class="font-weight-bold">{{ $loan->assignment->asset->brand }}</span> {{ $loan->assignment->asset->model }}
                                        <code class="d-block text-xs">{{ $loan->assignment->asset->asset_code }}</code>
                                    @else
                                        <span class="text-muted text-sm">Asset tidak ditemukan</span>
                                    @endif
                                </td>
                                <td>{{ $loan->loan_date ? $loan->loan_date->format('d-m-Y') : '-' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ strtoupper($loan->guarantee_type) }}</span>
                                </td>
                                <td><code>{{ $loan->guarantee_number }}</code></td>
                                <td>
                                    {{ $loan->return_date ? $loan->return_date->format('d-m-Y') : '-' }}
                                </td>
                                <td>
                                    @if($loan->status === 'borrowed')
                                        <span class="badge bg-warning text-dark">Borrowed (Sedang Dipinjam)</span>
                                    @else
                                        <span class="badge bg-success">Returned (Sudah Kembali)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($loan->status === 'borrowed')
                                        <form action="{{ route('admin.inventory.loans.return', $loan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin laptop ini sudah dikembalikan?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check-circle mr-1"></i> Return Laptop
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-check-double mr-1"></i> Selesai
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data peminjaman laptop.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $loans->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function showToast(message, type = 'success') {
            let toast = document.createElement('div');
            toast.className = 'toast-custom toast-' + type;
            toast.innerHTML = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => { toast.remove(); }, 500);
            }, 3000);
        }
    </script>
    <script>
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif
    </script>
@stop

@section('css')
    <style>
        .toast-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 280px;
            padding: 15px 20px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            z-index: 9999;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: slideIn 0.5s ease;
        }
        .toast-success { background: #28a745; }
        .toast-error { background: #dc3545; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
@stop
