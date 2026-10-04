@extends('adminlte::page')

@section('title', 'Manajemen Template Chat')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">
            <i class="fas fa-comments text-primary mr-2"></i>Manajemen Template Chat
        </h1>
        <p class="text-muted text-sm mb-0">Kelola balasan pesan cepat (quick replies) untuk Live Chat IT Support</p>
    </div>
    <div class="mt-2 mt-md-0">
        <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#modal-add-template">
            <i class="fas fa-plus-circle mr-1"></i> Tambah Template Baru
        </button>
    </div>
</div>
@stop

@section('css')
<style>
    /* Scope strictly to template tabs and cards to avoid touching sidebar menu */
    .template-card {
        border-top: 3px solid #007bff !important;
        border-radius: 8px;
    }
    #template-tabs.nav-pills .nav-link {
        border-radius: 6px;
        color: #495057;
        font-weight: 600;
        padding: 8px 16px;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        margin-right: 6px;
        margin-bottom: 4px;
        transition: all 0.2s ease;
    }
    #template-tabs.nav-pills .nav-link:hover {
        background-color: #f0f7ff;
        color: #007bff;
        border-color: #b8daff;
    }
    #template-tabs.nav-pills .nav-link.active {
        background-color: #007bff !important;
        color: #ffffff !important;
        border-color: #007bff !important;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.25);
    }
    #template-tabs.nav-pills .nav-link .badge-count {
        background-color: rgba(0, 0, 0, 0.08);
        color: inherit;
    }
    #template-tabs.nav-pills .nav-link.active .badge-count {
        background-color: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    .var-chip {
        border: 1px solid #b8daff;
        background-color: #f0f7ff;
        color: #0056b3;
        font-size: 11px;
        font-weight: 600;
        border-radius: 12px;
        padding: 3px 10px;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .var-chip:hover {
        background-color: #007bff;
        color: #ffffff;
        border-color: #007bff;
        transform: translateY(-1px);
    }
    .table-template tbody tr:hover {
        background-color: #f8fbff !important;
    }
</style>
@stop

@section('content')

@include('partials.floating_toast')

@if(session('success'))
    <div class="alert alert-primary alert-dismissible fade show font-weight-semibold shadow-xs bg-white text-primary border-primary" role="alert" style="border-left: 4px solid #007bff !important;">
        <i class="fas fa-check-circle mr-2 text-primary"></i>{{ session('success') }}
        <button type="button" class="close text-primary" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show font-weight-semibold shadow-xs" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
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

<div class="card template-card shadow-sm bg-white">
    <div class="card-header bg-light p-2.5 border-bottom">
        <ul class="nav nav-pills" id="template-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'all']) }}">
                    <i class="fas fa-layer-group mr-1.5"></i> Semua Template 
                    <span class="badge badge-count ml-1">{{ $counts['all'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'on_progress' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'on_progress']) }}">
                    On Progress 
                    
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'pending']) }}">
                     Pending 
                    
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'closed' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'closed']) }}">
                     Closed 
                    
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'general']) }}">
                    Umum 
                    
                </a>
            </li>
        </ul>
    </div>
    
    <div class="card-body p-3 bg-white">
        <div class="table-responsive">
            <table class="table table-hover table-template align-middle mb-0" id="table-chat-templates">
                <thead class="bg-light text-dark">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 220px;">Judul Template</th>
                        <th class="text-center" style="width: 140px;">Kategori</th>
                        <th>Isi Pesan</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td class="text-center font-weight-bold text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <b class="text-dark">{{ $template->title }}</b>
                            @if(!empty($template->shortcut))
                                <span class="badge badge-light border border-primary text-primary ml-1 font-weight-bold" title="Ketik /{{ $template->shortcut }} di live chat">/{{ $template->shortcut }}</span>
                            @endif
                            @if($template->order_index > 0)
                                <span class="badge badge-light border ml-1 text-secondary" title="Urutan prioritas">#{{ $template->order_index }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($template->category === 'on_progress')
                                <span class="badge badge-primary px-2.5 py-1 text-uppercase font-weight-bold">
                                    On Progress
                                </span>
                            @elseif($template->category === 'pending')
                                <span class="badge badge-info px-2.5 py-1 text-uppercase font-weight-bold">
                                    Pending
                                </span>
                            @elseif($template->category === 'closed')
                                <span class="badge badge-secondary px-2.5 py-1 text-uppercase font-weight-bold" style="background-color: #0284c7;">
                                    Closed
                                </span>
                            @else
                                <span class="badge badge-light border text-dark px-2.5 py-1 text-uppercase font-weight-bold">
                                    Umum
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="text-sm p-2.5 rounded bg-light border text-dark" style="max-height: 80px; overflow-y: auto; white-space: pre-wrap; font-family: inherit;">{{ $template->message }}</div>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('admin.chat-templates.toggle', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs font-weight-bold {{ $template->is_active ? 'btn-primary' : 'btn-outline-secondary' }}" title="Klik untuk mengubah status aktif">
                                    {{ $template->is_active ? '✓ Aktif' : 'Non-Aktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('admin.chat-templates.edit', $template->id) }}" class="btn btn-outline-primary btn-xs font-weight-bold mr-1 shadow-2xs" title="Edit Template">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.chat-templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template \'{{ $template->title }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs font-weight-bold shadow-2xs" title="Hapus Template">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x mb-3 text-primary d-block" style="opacity: 0.3;"></i>
                            <b>Belum ada template chat pada kategori ini.</b>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#modal-add-template">
                                    <i class="fas fa-plus mr-1"></i> Buat Template Sekarang
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Template (Clean White & Blue) -->
<div class="modal fade" id="modal-add-template" tabindex="-1" role="dialog" aria-labelledby="modalAddTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.chat-templates.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title font-weight-bold" id="modalAddTitle">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Template Chat Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label class="font-weight-bold text-dark">Judul Template <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control border-light shadow-2xs" placeholder="Contoh: Setting Printer" required maxlength="100">
                            <small class="text-muted">Nama singkat / label tombol quick reply.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="font-weight-bold text-dark">Shortcut <span class="text-muted font-weight-normal">(Opsional)</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-light font-weight-bold text-primary">/</span>
                                </div>
                                <input type="text" name="shortcut" class="form-control border-light shadow-2xs" placeholder="printer" maxlength="50">
                            </div>
                            <small class="text-muted">Ketik <code>/printer</code> di live chat.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold text-dark">Kategori Status <span class="text-danger">*</span></label>
                            <select name="category" class="form-control font-weight-bold border-light shadow-2xs" required>
                                <option value="on_progress" {{ $activeTab === 'on_progress' ? 'selected' : '' }}>🔵 On Progress</option>
                                <option value="pending" {{ $activeTab === 'pending' ? 'selected' : '' }}>🟡 Pending</option>
                                <option value="closed" {{ $activeTab === 'closed' ? 'selected' : '' }}>🟢 Closed</option>
                                <option value="general" {{ $activeTab === 'general' ? 'selected' : '' }}>⚪ Umum / General</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center">
                            <span>Isi Pesan Template <span class="text-danger">*</span></span>
                            <small class="text-muted font-weight-normal">Klik tombol di bawah untuk menyisipkan variabel:</small>
                        </label>
                        
                        <!-- Variable Helper Chips -->
                        <div class="mb-2 d-flex flex-wrap" style="gap: 6px;">
                            <button type="button" class="var-chip" onclick="insertVariable('add-message-input', '{user_name}')">
                                + {user_name} <span class="text-muted font-weight-normal">(Nama User)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('add-message-input', '{nomor_laptop}')">
                                + {nomor_laptop} <span class="text-muted font-weight-normal">(No. Laptop)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('add-message-input', '{ticket_code}')">
                                + {ticket_code} <span class="text-muted font-weight-normal">(Kode Tiket)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('add-message-input', '{admin_name}')">
                                + {admin_name} <span class="text-muted font-weight-normal">(Nama Admin)</span>
                            </button>
                            <button type="button" class="var-chip" onclick="insertVariable('add-message-input', '{kategori}')">
                                + {kategori} <span class="text-muted font-weight-normal">(Kategori)</span>
                            </button>
                        </div>

                        <textarea name="message" id="add-message-input" rows="4" class="form-control border-light shadow-2xs" placeholder="Tuliskan isi pesan balasan..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-bold text-dark">Urutan Tampilan</label>
                            <input type="number" name="order_index" class="form-control border-light shadow-2xs" value="0" min="0">
                            <small class="text-muted">Nomor lebih kecil akan tampil lebih awal di tombol live chat.</small>
                        </div>
                        <div class="col-md-6 form-group mb-0 d-flex align-items-center pt-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="add-is-active" value="1" checked>
                                <label class="custom-control-label font-weight-bold text-dark" for="add-is-active">Aktifkan Template Sekarang</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-outline-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
