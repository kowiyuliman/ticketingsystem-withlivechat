@extends('adminlte::page')
@section('title', 'Assign Asset ke User')

@section('content_header')
    <h1>Assign Asset ke User</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Form Assignment Set Asset (Satu User Satu Set Asset)</h3>
                </div>
                <form action="{{ route('admin.inventory.assignments.store') }}" method="POST">
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
                            <label for="user_id">Nama User <span class="text-danger">*</span></label>
                            <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3 text-primary"><i class="fas fa-desktop mr-1"></i> Pilih Item Pendukung (Kosongkan jika tidak ada)</h5>

                        <!-- Laptop -->
                        <div class="form-group">
                            <label for="laptop_id">Laptop</label>
                            <select name="laptop_id" id="laptop_id" class="form-control">
                                <option value="">-- Pilih Laptop --</option>
                                @foreach($laptops as $laptop)
                                    <option value="{{ $laptop->id }}" {{ old('laptop_id') == $laptop->id ? 'selected' : '' }}>
                                        {{ $laptop->asset_code }} - {{ $laptop->brand }} {{ $laptop->model }} (SN: {{ $laptop->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Charger -->
                        <div class="form-group">
                            <label for="charger_id">Charger</label>
                            <select name="charger_id" id="charger_id" class="form-control">
                                <option value="">-- Pilih Charger --</option>
                                @foreach($chargers as $charger)
                                    <option value="{{ $charger->id }}" {{ old('charger_id') == $charger->id ? 'selected' : '' }}>
                                        {{ $charger->asset_code }} - {{ $charger->brand ?? 'Charger' }} {{ $charger->model ?? '' }} (SN: {{ $charger->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Mouse -->
                        <div class="form-group">
                            <label for="mouse_id">Mouse</label>
                            <select name="mouse_id" id="mouse_id" class="form-control">
                                <option value="">-- Pilih Mouse --</option>
                                @foreach($mice as $mouse)
                                    <option value="{{ $mouse->id }}" {{ old('mouse_id') == $mouse->id ? 'selected' : '' }}>
                                        {{ $mouse->asset_code }} - {{ $mouse->brand ?? 'Mouse' }} {{ $mouse->model ?? '' }} (SN: {{ $mouse->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- LAN Adapter -->
                        <div class="form-group">
                            <label for="lan_adapter_id">LAN Adapter</label>
                            <select name="lan_adapter_id" id="lan_adapter_id" class="form-control">
                                <option value="">-- Pilih LAN Adapter --</option>
                                @foreach($lanAdapters as $lan)
                                    <option value="{{ $lan->id }}" {{ old('lan_adapter_id') == $lan->id ? 'selected' : '' }}>
                                        {{ $lan->asset_code }} - {{ $lan->brand ?? 'LAN Adapter' }} {{ $lan->model ?? '' }} (SN: {{ $lan->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Headset -->
                        <div class="form-group">
                            <label for="headset_id">Headset</label>
                            <select name="headset_id" id="headset_id" class="form-control">
                                <option value="">-- Pilih Headset --</option>
                                @foreach($headsets as $headset)
                                    <option value="{{ $headset->id }}" {{ old('headset_id') == $headset->id ? 'selected' : '' }}>
                                        {{ $headset->asset_code }} - {{ $headset->brand ?? 'Headset' }} {{ $headset->model ?? '' }} (SN: {{ $headset->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- USB Audio -->
                        <div class="form-group">
                            <label for="usb_audio_id">USB Audio</label>
                            <select name="usb_audio_id" id="usb_audio_id" class="form-control">
                                <option value="">-- Pilih USB Audio --</option>
                                @foreach($usbAudios as $usb)
                                    <option value="{{ $usb->id }}" {{ old('usb_audio_id') == $usb->id ? 'selected' : '' }}>
                                        {{ $usb->asset_code }} - {{ $usb->brand ?? 'USB Audio' }} {{ $usb->model ?? '' }} (SN: {{ $usb->serial_number ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Assign Assets</button>
                        <a href="{{ route('admin.inventory.assignments') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- CSS Select2 is preloaded in AdminLTE if configured, but we can make it a standard select since we don't have Select2 loaded explicitly -->
@stop

@section('js')
    <!-- Standard jQuery behavior if needed -->
@stop
