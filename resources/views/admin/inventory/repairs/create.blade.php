@extends('adminlte::page')
@section('title', 'Mulai Repair Asset')

@section('content_header')
    <h1>Mulai Repair Asset</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Form Laporan Kerusakan & Pengiriman Repair</h3>
                </div>
                <form action="{{ route('admin.inventory.repairs.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="asset_id">Pilih Asset <span class="text-danger">*</span></label>
                            <select name="asset_id" id="asset_id" class="form-control select2 @error('asset_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Asset --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->asset_code }} - {{ $asset->type_label }} ({{ $asset->brand ?? '' }} {{ $asset->model ?? '' }}) - Status: {{ $asset->status_label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan asset yang tidak sedang dalam status In Repair atau Damaged.</small>
                            @error('asset_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="repair_date">Tanggal Mulai Repair <span class="text-danger">*</span></label>
                            <input type="date" name="repair_date" id="repair_date" class="form-control @error('repair_date') is-invalid @enderror" value="{{ old('repair_date', now()->toDateString()) }}" required>
                            @error('repair_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="issue_description">Deskripsi Kerusakan / Kendala <span class="text-danger">*</span></label>
                            <textarea name="issue_description" id="issue_description" class="form-control @error('issue_description') is-invalid @enderror" rows="4" placeholder="Jelaskan masalah teknis pada asset..." required>{{ old('issue_description') }}</textarea>
                            @error('issue_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="repair_vendor">Vendor / Tempat Service</label>
                            <input type="text" name="repair_vendor" id="repair_vendor" class="form-control @error('repair_vendor') is-invalid @enderror" value="{{ old('repair_vendor') }}" placeholder="Contoh: Asus Service Center, Asus Partner, dll">
                            @error('repair_vendor')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="repair_cost">Estimasi Biaya Awal (Rp)</label>
                            <input type="number" name="repair_cost" id="repair_cost" class="form-control @error('repair_cost') is-invalid @enderror" value="{{ old('repair_cost') }}" placeholder="Masukkan nominal jika sudah ada estimasi" min="0">
                            @error('repair_cost')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-tools mr-1"></i> Kirim ke Repair</button>
                        <a href="{{ route('admin.inventory.repairs') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
