@extends('adminlte::page')
@section('title', 'Edit Asset')

@section('content_header')
    <h1>Edit Asset - {{ $asset->asset_code }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Asset</h3>
                </div>
                <form action="{{ route('admin.inventory.assets.update', $asset->id) }}" method="POST">
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
                            <label for="type">Tipe Asset <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $asset->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Brand: HP Dell Lenovo for Laptop, custom input for others -->
                        <div class="form-group" id="brand-group">
                            <label for="brand" id="brand-label">Brand <span class="text-danger">*</span></label>
                            <select id="brand-select" class="form-control @error('brand') is-invalid @enderror">
                                <option value="">-- Pilih Brand --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}" {{ old('brand', $asset->brand) == $brand ? 'selected' : '' }}>{{ $brand }}</option>
                                @endforeach
                            </select>
                            <input type="text" id="brand-input" class="form-control" placeholder="Masukkan merk..." value="{{ old('brand', $asset->brand) }}" style="display:none;">
                            @error('brand')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="model">Model / Tipe Spesifik</label>
                            <input type="text" name="model" id="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model', $asset->model) }}" placeholder="Contoh: EliteBook 840 G8, G502 Hero, dll">
                            @error('model')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="serial_number">Serial Number / Unique ID</label>
                            <input type="text" name="serial_number" id="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $asset->serial_number) }}" placeholder="Masukkan Serial Number">
                            @error('serial_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="condition">Kondisi <span class="text-danger">*</span></label>
                            <select name="condition" id="condition" class="form-control @error('condition') is-invalid @enderror" required>
                                <option value="good" {{ old('condition', $asset->condition) == 'good' ? 'selected' : '' }}>Good (Siap pakai)</option>
                                <option value="repair" {{ old('condition', $asset->condition) == 'repair' ? 'selected' : '' }}>Repair (Butuh perbaikan)</option>
                                <option value="damaged" {{ old('condition', $asset->condition) == 'damaged' ? 'selected' : '' }}>Damaged (Rusak total)</option>
                            </select>
                            @error('condition')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status Asset <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="available" {{ old('status', $asset->status) == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="assigned" {{ old('status', $asset->status) == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="on_loan" {{ old('status', $asset->status) == 'on_loan' ? 'selected' : '' }}>On Loan</option>
                                <option value="in_repair" {{ old('status', $asset->status) == 'in_repair' ? 'selected' : '' }}>In Repair</option>
                                <option value="damaged" {{ old('status', $asset->status) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes">Catatan Tambahan</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Tambahkan spesifikasi singkat, kelengkapan, atau kondisi khusus...">{{ old('notes', $asset->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Perbarui Asset</button>
                        <a href="{{ route('admin.inventory.assets') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            function toggleBrandFields() {
                var type = $('#type').val();
                if (type === 'laptop') {
                    $('#brand-group').show();
                    $('#brand-select').show().prop('disabled', false).prop('required', true);
                    $('#brand-input').hide().prop('disabled', true);
                    $('#brand-label').html('Brand <span class="text-danger">*</span>');
                } else if (type === '') {
                    $('#brand-group').hide();
                    $('#brand-select').prop('required', false);
                } else {
                    $('#brand-group').show();
                    $('#brand-select').hide().prop('disabled', true).prop('required', false);
                    $('#brand-input').show().prop('disabled', false);
                    $('#brand-label').text('Brand (Merk)');
                }
            }

            $('#type').change(toggleBrandFields);
            toggleBrandFields(); // Run on load to restore state

            $('form').submit(function() {
                var type = $('#type').val();
                if (type !== 'laptop' && type !== '') {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'brand',
                        value: $('#brand-input').val()
                    }).appendTo(this);
                } else if (type === 'laptop') {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'brand',
                        value: $('#brand-select').val()
                    }).appendTo(this);
                }
            });
        });
    </script>
@stop
