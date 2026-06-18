@extends('adminlte::page')
@section('title', 'Data Assets')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Data Assets</h1>
        <div>
            <button class="btn btn-success shadow-sm mr-2" data-toggle="modal" data-target="#modal-import-assets">
                <i class="fas fa-file-excel mr-1"></i> Import Spreadsheet
            </button>
            <a href="{{ route('admin.inventory.assets.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Asset
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info">
            <h3 class="card-title mb-0 text-white"><i class="fas fa-filter mr-1"></i> Filter Assets</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.inventory.assets') }}" method="GET" class="row">
                <div class="col-md-4 mb-2">
                    <label for="type">Tipe Asset</label>
                    <select name="type" id="type" class="form-control">
                        <option value="">-- Semua Tipe --</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label for="status">Status Asset</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Filter</button>
                    <a href="{{ route('admin.inventory.assets') }}" class="btn btn-secondary"><i class="fas fa-undo mr-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table-assets" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Asset</th>
                            <th>Tipe</th>
                            <th>Merk</th>
                            <th>Model</th>
                            <th>Serial Number</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            <tr>
                                <td>{{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}</td>
                                <td class="font-weight-bold">{{ $asset->asset_code }}</td>
                                <td>{{ $asset->type_label }}</td>
                                <td>{{ $asset->brand ?? '-' }}</td>
                                <td>{{ $asset->model ?? '-' }}</td>
                                <td><code>{{ $asset->serial_number ?? '-' }}</code></td>
                                <td>
                                    @if($asset->condition === 'good')
                                        <span class="badge bg-success">Good</span>
                                    @elseif($asset->condition === 'repair')
                                        <span class="badge bg-warning text-dark">Repair</span>
                                    @else
                                        <span class="badge bg-danger">Damaged</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asset->status_color }}">{{ $asset->status_label }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.inventory.assets.edit', $asset->id) }}" class="btn btn-warning btn-sm" title="Edit Asset">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.inventory.assets.delete', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asset ini?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Asset">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data asset.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $assets->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Modal Import Spreadsheet -->
    <div class="modal fade" id="modal-import-assets" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="importModalLabel"><i class="fas fa-file-excel mr-1"></i> Import Asset dari Spreadsheet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.inventory.assets.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info-circle"></i> Petunjuk Import</h5>
                            Format file harus berupa <strong>.xlsx</strong>, <strong>.xls</strong>, atau <strong>.csv</strong>. Gunakan template resmi di bawah ini agar kolom sesuai.
                            <div class="mt-2">
                                <a href="{{ route('admin.inventory.assets.download-template') }}" class="btn btn-sm btn-light font-weight-bold text-success">
                                    <i class="fas fa-download mr-1"></i> Download Template CSV
                                </a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="import-file">Pilih File Spreadsheet <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" name="file" class="custom-file-input" id="import-file" accept=".xlsx,.xls,.csv" required>
                                <label class="custom-file-label" for="import-file">Pilih file...</label>
                            </div>
                        </div>
                        
                        <div class="card card-secondary card-outline mt-3">
                            <div class="card-body p-2 text-sm">
                                <strong class="d-block mb-1 text-secondary">Ketentuan Nilai Kolom:</strong>
                                <ul class="pl-3 mb-0">
                                    <li><strong>type</strong>: <code>laptop</code>, <code>charger</code>, <code>mouse</code>, <code>lan_adapter</code>, <code>headset</code>, <code>usb_audio</code></li>
                                    <li><strong>condition</strong>: <code>good</code>, <code>repair</code>, <code>damaged</code></li>
                                    <li><strong>serial_number</strong>: Harus unik (tidak boleh ganda di sistem).</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success font-weight-bold"><i class="fas fa-upload mr-1"></i> Mulai Import</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Custom showToast function from main dashboard page
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
    <script>
        $(document).ready(function() {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
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
