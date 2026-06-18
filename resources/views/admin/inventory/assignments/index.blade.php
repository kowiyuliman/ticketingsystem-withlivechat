@extends('adminlte::page')
@section('title', 'Asset Assignments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Asset Assignments</h1>
        <a href="{{ route('admin.inventory.assignments.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus mr-1"></i> Assign Asset ke User
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-header bg-info">
            <h3 class="card-title mb-0 text-white"><i class="fas fa-user-tag mr-1"></i> Daftar Kepemilikan Asset Aktif (Satu User Satu Set Asset)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="text-center bg-light">
                            <th rowspan="2" class="align-middle">No</th>
                            <th rowspan="2" class="align-middle">Nama User</th>
                            <th colspan="6">Rincian Asset Handover</th>
                        </tr>
                        <tr class="text-center bg-light">
                            <th>Laptop</th>
                            <th>Charger</th>
                            <th>Mouse</th>
                            <th>LAN Adapter</th>
                            <th>Headset</th>
                            <th>USB Audio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $laptopAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'laptop');
                                $chargerAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'charger');
                                $mouseAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'mouse');
                                $lanAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'lan_adapter');
                                $headsetAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'headset');
                                $usbAssignment = $user->assignments->first(fn($a) => $a->asset->type === 'usb_audio');
                            @endphp
                            <tr>
                                <td class="text-center align-middle">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                <td class="align-middle font-weight-bold">
                                    {{ $user->name }}
                                    <span class="d-block text-muted text-sm">{{ $user->username }}</span>
                                </td>
                                
                                <!-- Laptop -->
                                <td class="align-middle text-center">
                                    @if($laptopAssignment)
                                        <span class="d-block font-weight-bold">{{ $laptopAssignment->asset->brand }}</span>
                                        <span class="d-block text-sm text-muted">{{ $laptopAssignment->asset->model }}</span>
                                        <code class="d-block text-xs">{{ $laptopAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $laptopAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan laptop ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign Laptop">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>

                                <!-- Charger -->
                                <td class="align-middle text-center">
                                    @if($chargerAssignment)
                                        <span class="d-block font-weight-bold">{{ $chargerAssignment->asset->brand ?? 'Charger' }}</span>
                                        <code class="d-block text-xs">{{ $chargerAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $chargerAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan charger ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign Charger">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>

                                <!-- Mouse -->
                                <td class="align-middle text-center">
                                    @if($mouseAssignment)
                                        <span class="d-block font-weight-bold">{{ $mouseAssignment->asset->brand ?? 'Mouse' }}</span>
                                        <code class="d-block text-xs">{{ $mouseAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $mouseAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan mouse ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign Mouse">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>

                                <!-- LAN Adapter -->
                                <td class="align-middle text-center">
                                    @if($lanAssignment)
                                        <span class="d-block font-weight-bold">{{ $lanAssignment->asset->brand ?? 'LAN Adapter' }}</span>
                                        <code class="d-block text-xs">{{ $lanAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $lanAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan LAN Adapter ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign LAN Adapter">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>

                                <!-- Headset -->
                                <td class="align-middle text-center">
                                    @if($headsetAssignment)
                                        <span class="d-block font-weight-bold">{{ $headsetAssignment->asset->brand ?? 'Headset' }}</span>
                                        <code class="d-block text-xs">{{ $headsetAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $headsetAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan headset ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign Headset">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>

                                <!-- USB Audio -->
                                <td class="align-middle text-center">
                                    @if($usbAssignment)
                                        <span class="d-block font-weight-bold">{{ $usbAssignment->asset->brand ?? 'USB Audio' }}</span>
                                        <code class="d-block text-xs">{{ $usbAssignment->asset->asset_code }}</code>
                                        <form action="{{ route('admin.inventory.assignments.return', $usbAssignment->id) }}" method="POST" onsubmit="return confirm('Kembalikan USB Audio ini?')" class="mt-1">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Unassign USB Audio">
                                                <i class="fas fa-undo"></i> Return
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada assignment aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links('pagination::bootstrap-4') }}
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
