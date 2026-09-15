@extends('adminlte::page')

@section('title', 'Tambah Admin Baru')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-user-plus text-primary mr-2"></i>Tambah Admin Baru</h1>
        <p class="text-muted text-sm mb-0">Lengkapi formulir di bawah ini untuk mendaftarkan akun administrator baru</p>
    </div>
    <div>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Admin
        </a>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

<div class="row justify-content-center">
    <div class="col-lg-7 col-md-10 col-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-id-card text-primary mr-1"></i> Form Akun Administrator
                </h3>
            </div>
            
            <form action="{{ route('admin.admins.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="card-body p-4">
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Mohon periksa kesalahan input:</h6>
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- NAMA LENGKAP --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark" for="name">
                            <i class="fas fa-user text-secondary mr-1"></i> Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               placeholder="Contoh: Budi Santoso, S.Kom" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nama lengkap administrator yang akan ditampilkan pada tiket dan histori.</small>
                    </div>

                    {{-- USERNAME --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark" for="username">
                            <i class="fas fa-at text-secondary mr-1"></i> Username <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light font-weight-bold">@</span>
                            </div>
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   class="form-control form-control-lg @error('username') is-invalid @enderror" 
                                   placeholder="Contoh: budi.it" 
                                   value="{{ old('username') }}" 
                                   required 
                                   style="text-transform: lowercase;">
                            @error('username')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Digunakan untuk login ke panel admin (otomatis huruf kecil tanpa spasi).</small>
                    </div>

                    {{-- PASSWORD --}}
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark" for="password">
                            <i class="fas fa-key text-secondary mr-1"></i> Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   placeholder="Minimal 6 karakter" 
                                   required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Minimal 6 karakter untuk keamanan hak akses administrator.</small>
                    </div>

                    {{-- ROLE SELECTION --}}
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark" for="role">
                            <i class="fas fa-user-tag text-secondary mr-1"></i> Role Hak Akses <span class="text-danger">*</span>
                        </label>
                        <select name="role" id="role" class="form-control form-control-lg @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ old('role', 'admin') == 'admin' ? 'selected' : '' }}>
                                🛡️ Admin / IT Support (Full Access - Kelola Tiket, Aset & Admin)
                            </option>
                            <option value="management" {{ old('role') == 'management' ? 'selected' : '' }}>
                                👔 Management / Bos (Hanya Dashboard Monitoring)
                            </option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Pilih <b>Admin</b> untuk teknisi/operator IT, atau pilih <b>Management</b> untuk pimpinan/bos yang hanya melihat dashboard monitoring.
                        </small>
                    </div>

                </div>

                <div class="card-footer bg-light d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-default font-weight-bold">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@stop

