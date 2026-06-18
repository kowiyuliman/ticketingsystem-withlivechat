@extends('adminlte::page')
@section('title', 'Asset Rusak')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Management Asset Rusak</h1>
        <a href="{{ route('admin.inventory.damages.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Laporkan Asset Rusak
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-info">
            <h3 class="card-title mb-0 text-white"><i class="fas fa-exclamation-triangle mr-1"></i> Daftar Kerusakan & Penanganan Asset</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Asset</th>
                            <th>Pelapor</th>
                            <th>Tanggal Rusak</th>
                            <th>Deskripsi Kerusakan</th>
                            <th>Tindakan Penanganan</th>
                            <th>Asset Pengganti</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($damages as $damage)
                            <tr>
                                <td>{{ ($damages->currentPage() - 1) * $damages->perPage() + $loop->iteration }}</td>
                                <td>
                                    <span class="font-weight-bold d-block">{{ $damage->asset->type_label }} - {{ $damage->asset->brand ?? '' }}</span>
                                    <span class="text-sm text-muted d-block">{{ $damage->asset->model }}</span>
                                    <code class="text-xs">{{ $damage->asset->asset_code }}</code>
                                </td>
                                <td>{{ $damage->reporter->name }}</td>
                                <td>{{ $damage->damage_date ? $damage->damage_date->format('d-m-Y') : '-' }}</td>
                                <td>{{ $damage->damage_description }}</td>
                                <td>
                                    @if($damage->action_taken === 'pending')
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock mr-1"></i> Pending Action</span>
                                    @elseif($damage->action_taken === 'repair')
                                        <span class="badge bg-info"><i class="fas fa-tools mr-1"></i> Sent to Repair</span>
                                    @elseif($damage->action_taken === 'dispose')
                                        <span class="badge bg-danger"><i class="fas fa-trash-alt mr-1"></i> Disposed (Scrapped)</span>
                                    @else
                                        <span class="badge bg-success"><i class="fas fa-exchange-alt mr-1"></i> Replaced</span>
                                    @endif
                                </td>
                                <td>
                                    @if($damage->replacementAsset)
                                        <span class="font-weight-bold d-block">{{ $damage->replacementAsset->brand }}</span>
                                        <code class="text-xs">{{ $damage->replacementAsset->asset_code }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $damage->notes ?? '-' }}</td>
                                <td>
                                    @if($damage->action_taken === 'pending')
                                        <button class="btn btn-warning btn-sm btn-action-damage"
                                                data-id="{{ $damage->id }}"
                                                data-asset="{{ $damage->asset->asset_code }} - {{ $damage->asset->brand }} {{ $damage->asset->model }}"
                                                data-type="{{ $damage->asset->type }}"
                                                data-toggle="modal"
                                                data-target="#modal-action-damage">
                                            <i class="fas fa-gavel mr-1"></i> Tindak Lanjuti
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
                                <td colspan="9" class="text-center">Tidak ada laporan asset rusak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $damages->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal Action Damage -->
    <div class="modal fade" id="modal-action-damage" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold" id="modalLabel"><i class="fas fa-gavel mr-1"></i> Tindakan Penanganan Asset Rusak</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-update-damage" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Asset Rusak</label>
                            <input type="text" id="modal-asset-info" class="form-control-plaintext font-weight-bold" readonly value="">
                        </div>

                        <div class="form-group">
                            <label for="action_taken">Pilih Tindakan Penanganan <span class="text-danger">*</span></label>
                            <select name="action_taken" id="modal-action" class="form-control" required>
                                <option value="repair">Kirim ke Repair (Status berubah menjadi In Repair)</option>
                                <option value="dispose">Dispose / Scrap (Asset dibuang / dihapus dari sistem)</option>
                                <option value="replace">Ganti Unit (Replace dengan Unit Ready)</option>
                            </select>
                        </div>

                        <!-- Replacement Asset Dropdown (Conditional) -->
                        <div class="form-group" id="replacement-field" style="display:none;">
                            <label for="modal-replacement-asset">Pilih Asset Pengganti <span class="text-danger">*</span></label>
                            <select name="replacement_asset_id" id="modal-replacement-asset" class="form-control">
                                <option value="">-- Pilih Unit Tersedia --</option>
                                @foreach($assetsAvailable as $avail)
                                    <option value="{{ $avail->id }}" data-type="{{ $avail->type }}">
                                        {{ $avail->asset_code }} - {{ $avail->brand }} {{ $avail->model }} (SN: {{ $avail->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan asset sejenis yang berstatus **Available**.</small>
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan Tindakan</label>
                            <textarea name="notes" id="modal-notes" class="form-control" rows="3" placeholder="Masukkan alasan tindakan atau rincian tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-save mr-1"></i> Simpan Penanganan</button>
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
            $('.btn-action-damage').click(function() {
                var damageId = $(this).data('id');
                var assetInfo = $(this).data('asset');
                var assetType = $(this).data('type');

                $('#modal-asset-info').val(assetInfo);
                
                // Hide all replacement options, then show only matching type
                $('#modal-replacement-asset option').each(function() {
                    var optType = $(this).data('type');
                    if (!optType || optType === assetType) {
                        $(this).show().prop('disabled', false);
                    } else {
                        $(this).hide().prop('disabled', true);
                    }
                });

                // Reset replacement select value
                $('#modal-replacement-asset').val('');
                
                // Toggle replacement fields on click
                toggleReplacementField();

                // Set form action route
                var routeUrl = "{{ route('admin.inventory.damages.update', ':id') }}";
                routeUrl = routeUrl.replace(':id', damageId);
                $('#form-update-damage').attr('action', routeUrl);
            });

            function toggleReplacementField() {
                if ($('#modal-action').val() === 'replace') {
                    $('#replacement-field').show();
                    $('#modal-replacement-asset').prop('required', true);
                } else {
                    $('#replacement-field').hide();
                    $('#modal-replacement-asset').prop('required', false);
                }
            }

            $('#modal-action').change(toggleReplacementField);
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
