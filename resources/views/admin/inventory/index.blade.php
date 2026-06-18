@extends('adminlte::page')
@section('title', 'Inventory Dashboard')

@section('content_header')
    <h1>Inventory Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <!-- Total Assets -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Assets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <a href="{{ route('admin.inventory.assets') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Available -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['available'] }}</h3>
                    <p>Available Assets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="{{ route('admin.inventory.assets') }}?status=available" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Assigned -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['assigned'] }}</h3>
                    <p>Assigned to Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-tag"></i>
                </div>
                <a href="{{ route('admin.inventory.assignments') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- On Loan -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['on_loan'] }}</h3>
                    <p>On Loan (Laptop Bawa Pulang)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <a href="{{ route('admin.inventory.loans') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- In Repair -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['in_repair'] }}</h3>
                    <p>In Repair (Maintenance)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <a href="{{ route('admin.inventory.repairs') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Damaged -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ $stats['damaged'] }}</h3>
                    <p>Damaged Assets</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.inventory.damages') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Navigation & Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('admin.inventory.assets.create') }}" class="btn btn-outline-primary btn-block text-left p-3">
                                <i class="fas fa-plus fa-2x mb-2 d-block"></i>
                                <strong>Tambah Asset Baru</strong>
                                <span class="d-block text-muted text-sm">Input data laptop, charger, dll.</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('admin.inventory.assignments.create') }}" class="btn btn-outline-success btn-block text-left p-3">
                                <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                <strong>Assign Asset</strong>
                                <span class="d-block text-muted text-sm">Berikan set asset ke user.</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('admin.inventory.loans.create') }}" class="btn btn-outline-warning btn-block text-left p-3">
                                <i class="fas fa-hand-holding fa-2x mb-2 d-block"></i>
                                <strong>Peminjaman Laptop</strong>
                                <span class="d-block text-muted text-sm">Laptop dibawa pulang + KTP/SIM.</span>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('admin.inventory.repairs.create') }}" class="btn btn-outline-indigo btn-block text-left p-3" style="border-color: #6610f2; color: #6610f2;">
                                <i class="fas fa-tools fa-2x mb-2 d-block"></i>
                                <strong>Kirim ke Repair</strong>
                                <span class="d-block text-muted text-sm">Maintenance / service asset.</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Asset Distribution</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Asset Type</th>
                                <th class="text-center">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($types as $key => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-center font-weight-bold">{{ $typeStats[$key] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box .icon {
            font-size: 60px;
            right: 15px;
            top: 15px;
            transition: transform 0.3s linear;
        }
        .small-box:hover .icon {
            transform: scale(1.1);
        }
    </style>
@stop
