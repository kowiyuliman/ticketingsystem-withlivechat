@extends('adminlte::page')
@section('title', 'Buat Peminjaman Laptop')

@section('content_header')
    <h1>Buat Peminjaman Laptop</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Form Peminjaman Laptop & Jaminan</h3>
                </div>
                <form action="{{ route('admin.inventory.loans.store') }}" method="POST">
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
                            <label for="asset_assignment_id">Pilih User & Laptop <span class="text-danger">*</span></label>
                            <select name="asset_assignment_id" id="asset_assignment_id" class="form-control select2 @error('asset_assignment_id') is-invalid @enderror" required>
                                <option value="">-- Pilih User & Laptop yang di-Assign --</option>
                                @foreach($activeLaptopAssignments as $assignment)
                                    <option value="{{ $assignment->id }}" {{ old('asset_assignment_id') == $assignment->id ? 'selected' : '' }}>
                                        {{ $assignment->user->name }} - {{ $assignment->asset->brand }} {{ $assignment->asset->model }} (Code: {{ $assignment->asset->asset_code }}, SN: {{ $assignment->asset->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hanya menampilkan user yang telah di-assign laptop dengan status **assigned** (belum dipinjam / direpair).</small>
                            @error('asset_assignment_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="loan_date">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <input type="date" name="loan_date" id="loan_date" class="form-control @error('loan_date') is-invalid @enderror" value="{{ old('loan_date', now()->toDateString()) }}" required>
                            @error('loan_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="guarantee_type">Jenis Jaminan <span class="text-danger">*</span></label>
                            <select name="guarantee_type" id="guarantee_type" class="form-control @error('guarantee_type') is-invalid @enderror" required>
                                <option value="ktp" {{ old('guarantee_type') == 'ktp' ? 'selected' : '' }}>KTP</option>
                                <option value="sim" {{ old('guarantee_type') == 'sim' ? 'selected' : '' }}>SIM</option>
                            </select>
                            @error('guarantee_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="guarantee_number">Nomor Identitas Jaminan <span class="text-danger">*</span></label>
                            <input type="text" name="guarantee_number" id="guarantee_number" class="form-control @error('guarantee_number') is-invalid @enderror" value="{{ old('guarantee_number') }}" placeholder="Masukkan nomor KTP/SIM jaminan" required>
                            @error('guarantee_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan Tambahan</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Tujuan pinjam, kelengkapan yang dibawa pulang (tas, mouse, charger dll)...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Peminjaman</button>
                        <a href="{{ route('admin.inventory.loans') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
