@extends('adminlte::page')
@section('title', 'Laporkan Asset Rusak')

@section('content_header')
    <h1>Laporkan Asset Rusak</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Form Laporan Kerusakan Asset</h3>
                </div>
                <form action="{{ route('admin.inventory.damages.store') }}" method="POST">
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
                            <label for="asset_id">Pilih Asset Yang Rusak <span class="text-danger">*</span></label>
                            <select name="asset_id" id="asset_id" class="form-control select2 @error('asset_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Asset --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->asset_code }} - {{ $asset->type_label }} ({{ $asset->brand ?? '' }} {{ $asset->model ?? '' }}) - Status: {{ $asset->status_label }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan asset yang tidak dalam status Damaged (Rusak).</small>
                            @error('asset_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="damage_date">Tanggal Terjadi Kerusakan <span class="text-danger">*</span></label>
                            <input type="date" name="damage_date" id="damage_date" class="form-control @error('damage_date') is-invalid @enderror" value="{{ old('damage_date', now()->toDateString()) }}" required>
                            @error('damage_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="damage_description">Deskripsi Kerusakan <span class="text-danger">*</span></label>
                            <textarea name="damage_description" id="damage_description" class="form-control @error('damage_description') is-invalid @enderror" rows="4" placeholder="Jelaskan detail kerusakan fisik/fungsional asset..." required>{{ old('damage_description') }}</textarea>
                            @error('damage_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Simpan Laporan</button>
                        <a href="{{ route('admin.inventory.damages') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
