@extends('adminlte::page')

@section('title', 'Edit Template Chat')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-edit text-primary mr-2"></i>Edit Template Chat
        </h1>
        <p class="text-muted text-sm mb-0">Perbarui isi pesan atau pengaturan template balasan cepat</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('admin.chat-templates.index', ['tab' => $template->category]) }}" class="btn btn-outline-secondary font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Template
        </a>
    </div>
</div>
@stop

@section('css')
<style>
    .template-card {
        border-top: 3px solid #007bff !important;
        border-radius: 8px;
    }
    .var-chip {
        border: 1px solid #b8daff;
        background-color: #f0f7ff;
        color: #0056b3;
        font-size: 11px;
        font-weight: 600;
        border-radius: 12px;
        padding: 4px 12px;
        transition: all 0.15s ease;
        cursor: pointer;
        display: inline-block;
    }
    .var-chip:hover {
        background-color: #007bff;
        color: #ffffff;
        border-color: #007bff;
        transform: translateY(-1px);
    }
</style>
@stop

@section('content')

@include('partials.floating_toast')

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 col-12">
        <div class="card template-card shadow-sm bg-white">
            <div class="card-header bg-light border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-comment-dots text-primary mr-1"></i> Edit Template: <u>{{ $template->title }}</u>
                </h3>
            </div>
            
            <form action="{{ route('admin.chat-templates.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body p-4 bg-white">
                    
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4 font-weight-semibold shadow-xs" role="alert">
                            <h6 class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Periksa kesalahan input:</h6>
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

                    <div class="row">
                        {{-- JUDUL TEMPLATE --}}
                        <div class="col-md-5 form-group mb-3">
                            <label class="font-weight-bold text-dark" for="title">
                                Judul Template <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   class="form-control border-light shadow-2xs @error('title') is-invalid @enderror" 
                                   placeholder="Contoh: Setting Printer" 
                                   value="{{ old('title', $template->title) }}" 
                                   required 
                                   maxlength="100">
                            <small class="text-muted">Nama singkat / label tombol quick reply.</small>
                            @error('title')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SHORTCUT / KODE CEPAT --}}
                        <div class="col-md-3 form-group mb-3">
                            <label class="font-weight-bold text-dark" for="shortcut">
                                Shortcut <span class="text-muted font-weight-normal">(Opsional)</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-light font-weight-bold text-primary">/</span>
                                </div>
                                <input type="text" 
                                       name="shortcut" 
                                       id="shortcut" 
                                       class="form-control border-light shadow-2xs @error('shortcut') is-invalid @enderror" 
                                       placeholder="printer" 
                                       value="{{ old('shortcut', $template->shortcut) }}" 
                                       maxlength="50">
                            </div>
                            <small class="text-muted">Ketik <code>/printer</code> di live chat.</small>
                            @error('shortcut')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- KATEGORI STATUS --}}
                        <div class="col-md-4 form-group mb-3">
                            <label class="font-weight-bold text-dark" for="category">
                                Kategori Status <span class="text-danger">*</span>
                            </label>
                            <select name="category" id="category" class="form-control font-weight-bold border-light shadow-2xs @error('category') is-invalid @enderror" required>
                                <option value="on_progress" {{ old('category', $template->category) === 'on_progress' ? 'selected' : '' }}>🔵 On Progress</option>
                                <option value="pending" {{ old('category', $template->category) === 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                                <option value="closed" {{ old('category', $template->category) === 'closed' ? 'selected' : '' }}>🟢 Closed</option>
                                <option value="general" {{ old('category', $template->category) === 'general' ? 'selected' : '' }}>⚪ Umum / General</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- ISI PESAN --}}
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center" for="message-input">
                            <span>Isi Pesan Template <span class="text-danger">*</span></span>
                            <small class="text-muted font-weight-normal">Klik tombol variabel di bawah untuk menyisipkan otomatis:</small>
                        </label>
                        
                        <!-- Variable Helper Chips -->
                        <div class="mb-2 d-flex flex-wrap" style="gap: 6px;">
                            <button type="button" class="var-chip" onclick="insertVariable('message-input', '{user_name}')">
                                + {user_name} <span class="text-muted font-weight-normal">(Nama User)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('message-input', '{nomor_laptop}')">
                                + {nomor_laptop} <span class="text-muted font-weight-normal">(No. Laptop)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('message-input', '{ticket_code}')">
                                + {ticket_code} <span class="text-muted font-weight-normal">(Kode Tiket)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('message-input', '{admin_name}')">
                                + {admin_name} <span class="text-muted font-weight-normal">(Nama Admin)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('message-input', '{kategori}')">
                                + {kategori} <span class="text-muted font-weight-normal">(Kategori)</span>
                            </button>
                        </div>

                        <textarea name="message" 
                                  id="message-input" 
                                  rows="5" 
                                  class="form-control border-light shadow-2xs @error('message') is-invalid @enderror" 
                                  placeholder="Tuliskan isi pesan balasan..." 
                                  required>{{ old('message', $template->message) }}</textarea>
                        @error('message')
                            <div class="invalid-feedback font-weight-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        {{-- URUTAN TAMPILAN --}}
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-bold text-dark" for="order_index">Urutan Tampilan</label>
                            <input type="number" 
                                   name="order_index" 
                                   id="order_index" 
                                   class="form-control border-light shadow-2xs" 
                                   value="{{ old('order_index', $template->order_index) }}" 
                                   min="0">
                            <small class="text-muted">Nomor lebih kecil akan tampil lebih awal di tombol live chat.</small>
                        </div>

                        {{-- STATUS AKTIF --}}
                        <div class="col-md-6 form-group mb-0 d-flex align-items-center pt-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" 
                                       name="is_active" 
                                       class="custom-control-input" 
                                       id="edit-is-active" 
                                       value="1" 
                                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="edit-is-active">Status Aktif (Tampilkan di Live Chat)</label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.chat-templates.index', ['tab' => $template->category]) }}" class="btn btn-outline-secondary font-weight-bold">
                        <i class="fas fa-times mr-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm px-4">
                        <i class="fas fa-save mr-1"></i> Perbarui Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')
@stop

@section('js')
<script>
    function insertVariable(inputId, variableTag) {
        const textarea = document.getElementById(inputId);
        if (!textarea) return;
        
        const start = textarea.selectionStart || textarea.value.length;
        const end = textarea.selectionEnd || textarea.value.length;
        const text = textarea.value;
        
        textarea.value = text.substring(0, start) + variableTag + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + variableTag.length;
    }
</script>
@stop
