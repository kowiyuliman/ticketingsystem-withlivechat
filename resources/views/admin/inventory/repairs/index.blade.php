@extends('adminlte::page')
@section('title', 'Asset Repairs')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Asset Repair & Maintenance</h1>
        <a href="{{ route('admin.inventory.repairs.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Mulai Repair
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-info">
            <h3 class="card-title mb-0 text-white"><i class="fas fa-wrench mr-1"></i> Daftar Pemeliharaan & Perbaikan Asset</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Asset</th>
                            <th>Pelapor</th>
                            <th>Deskripsi Masalah</th>
                            <th>Tanggal Masuk</th>
                            <th>Tanggal Selesai</th>
                            <th>Vendor</th>
                            <th>Biaya</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repairs as $repair)
                            <tr>
                                <td>{{ ($repairs->currentPage() - 1) * $repairs->perPage() + $loop->iteration }}</td>
                                <td>
                                    <span class="font-weight-bold d-block">{{ $repair->asset->type_label }} - {{ $repair->asset->brand ?? '' }}</span>
                                    <span class="text-sm text-muted d-block">{{ $repair->asset->model }} (SN: {{ $repair->asset->serial_number ?? '-' }})</span>
                                    <code class="text-xs">{{ $repair->asset->asset_code }}</code>
                                </td>
                                <td>{{ $repair->reporter->name }}</td>
                                <td>{{ $repair->issue_description }}</td>
                                <td>{{ $repair->repair_date ? $repair->repair_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ $repair->completed_date ? $repair->completed_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ $repair->repair_vendor ?? '-' }}</td>
                                <td>
                                    @if($repair->repair_cost)
                                        Rp {{ number_format($repair->repair_cost, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($repair->status === 'in_progress')
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    @elseif($repair->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($repair->status === 'in_progress')
                                        <button class="btn btn-warning btn-sm btn-update-status" 
                                                data-id="{{ $repair->id }}"
                                                data-asset="{{ $repair->asset->asset_code }} - {{ $repair->asset->brand }} {{ $repair->asset->model }}"
                                                data-vendor="{{ $repair->repair_vendor }}"
                                                data-cost="{{ $repair->repair_cost }}"
                                                data-toggle="modal" 
                                                data-target="#modal-update-repair"
                                                title="Selesaikan Perbaikan">
                                            <i class="fas fa-edit mr-1"></i> Update Status
                                        </button>
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-check-double mr-1"></i> Selesai
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada laporan repair.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $repairs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal Update Repair Status -->
    <div class="modal fade" id="modal-update-repair" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold" id="modalLabel"><i class="fas fa-tools mr-1"></i> Update Status Perbaikan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-update-repair" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Asset</label>
                            <input type="text" id="modal-asset-info" class="form-control-plaintext font-weight-bold" readonly value="">
                        </div>

                        <div class="form-group">
                            <label for="status">Pilih Status <span class="text-danger">*</span></label>
                            <select name="status" id="modal-status" class="form-control" required>
                                <option value="completed">Completed (Selesai & Bagus)</option>
                                <option value="cancelled">Cancelled (Batal/Tidak Diperbaiki)</option>
                            </select>
                        </div>

                        <div id="completed-fields">
                            <div class="form-group">
                                <label for="completed_date">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="completed_date" id="modal-completed-date" class="form-control" value="{{ now()->toDateString() }}">
                            </div>

                            <div class="form-group">
                                <label for="resolution">Catatan Resolusi / Tindakan <span class="text-danger">*</span></label>
                                <textarea name="resolution" id="modal-resolution" class="form-control" rows="3" placeholder="Jelaskan apa saja yang diperbaiki..."></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="repair_cost">Total Biaya Perbaikan (Rp)</label>
                            <input type="number" name="repair_cost" id="modal-repair-cost" class="form-control" placeholder="Masukkan nominal biaya" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Setup modal data
            $('.btn-update-status').click(function() {
                var repairId = $(this).data('id');
                var assetInfo = $(this).data('asset');
                var vendor = $(this).data('vendor');
                var cost = $(this).data('cost');

                $('#modal-asset-info').val(assetInfo);
                $('#modal-repair-cost').val(cost);
                
                // Set form action route dynamically
                var routeUrl = "{{ route('admin.inventory.repairs.update', ':id') }}";
                routeUrl = routeUrl.replace(':id', repairId);
                $('#form-update-repair').attr('action', routeUrl);
            });

            // Conditional fields based on status
            $('#modal-status').change(function() {
                if ($(this).val() === 'completed') {
                    $('#completed-fields').show();
                    $('#modal-completed-date').prop('required', true);
                    $('#modal-resolution').prop('required', true);
                } else {
                    $('#completed-fields').hide();
                    $('#modal-completed-date').prop('required', false);
                    $('#modal-resolution').prop('required', false);
                }
            });
        });
    </script>
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
