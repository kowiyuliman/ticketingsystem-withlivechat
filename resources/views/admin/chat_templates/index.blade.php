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

@section('content')

@include('partials.floating_toast')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show font-weight-semibold shadow-xs" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
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

<div class="card card-primary card-outline card-outline-tabs shadow-sm">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs font-weight-bold" id="template-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'all']) }}">
                    <i class="fas fa-layer-group mr-1"></i> Semua Template 
                    <span class="badge badge-secondary ml-1">{{ $counts['all'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'on_progress' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'on_progress']) }}">
                    <i class="fas fa-spinner mr-1 text-primary"></i> On Progress 
                    <span class="badge badge-primary ml-1">{{ $counts['on_progress'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'pending']) }}">
                    <i class="fas fa-clock mr-1 text-warning"></i> Pending 
                    <span class="badge badge-warning ml-1">{{ $counts['pending'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'closed' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'closed']) }}">
                    <i class="fas fa-check-circle mr-1 text-success"></i> Closed 
                    <span class="badge badge-success ml-1">{{ $counts['closed'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" href="{{ route('admin.chat-templates.index', ['tab' => 'general']) }}">
                    <i class="fas fa-comment-alt mr-1 text-secondary"></i> Umum 
                    <span class="badge badge-light border ml-1">{{ $counts['general'] }}</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="table-chat-templates">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 220px;">Judul Template</th>
                        <th class="text-center" style="width: 140px;">Kategori</th>
                        <th>Isi Pesan</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td class="text-center font-weight-bold">{{ $loop->iteration }}</td>
                        <td>
                            <b class="text-dark">{{ $template->title }}</b>
                            @if($template->order_index > 0)
                                <span class="badge badge-light border ml-1 text-muted" title="Urutan prioritas">#{{ $template->order_index }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $template->category_badge_class }} px-2.5 py-1 text-uppercase font-weight-bold">
                                {{ $template->category_label }}
                            </span>
                        </td>
                        <td>
                            <div class="text-sm p-2 rounded bg-white border" style="max-height: 80px; overflow-y: auto; white-space: pre-wrap;">{{ $template->message }}</div>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('admin.chat-templates.toggle', $template->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs font-weight-bold {{ $template->is_active ? 'btn-success' : 'btn-secondary' }}" title="Klik untuk mengubah status aktif">
                                    {{ $template->is_active ? '✓ Aktif' : 'Non-Aktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning btn-xs font-weight-bold mr-1 shadow-2xs" 
                                        onclick="openEditModal({{ json_encode($template) }})" title="Edit Template">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <form action="{{ route('admin.chat-templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template \'{{ $template->title }}\'?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs font-weight-bold shadow-2xs" title="Hapus Template">
                                        <i class="fas fa-trash-alt mr-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-comment-slash fa-3x mb-3 text-muted d-block"></i>
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

<!-- Modal Tambah Template -->
<div class="modal fade" id="modal-add-template" tabindex="-1" role="dialog" aria-labelledby="modalAddTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.chat-templates.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="modalAddTitle">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Template Chat Baru
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-7 form-group">
                            <label class="font-weight-bold text-dark">Judul Template <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Contoh: Konfirmasi Remote Pengerjaan" required maxlength="100">
                            <small class="text-muted">Nama singkat untuk label tombol quick reply di live chat.</small>
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="font-weight-bold text-dark">Kategori Status <span class="text-danger">*</span></label>
                            <select name="category" class="form-control font-weight-bold" required>
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
                            <small class="text-muted font-weight-normal">Klik variabel di bawah untuk menyisipkan otomatis:</small>
                        </label>
                        
                        <!-- Variable Helper Chips -->
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold mr-1 mb-1" onclick="insertVariable('add-message-input', '{user_name}')">
                                + {user_name} <small class="text-muted font-weight-normal">(Nama User)</small>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-info font-weight-bold mr-1 mb-1" onclick="insertVariable('add-message-input', '{nomor_laptop}')">
                                + {nomor_laptop} <small class="text-muted font-weight-normal">(No. Laptop)</small>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success font-weight-bold mr-1 mb-1" onclick="insertVariable('add-message-input', '{ticket_code}')">
                                + {ticket_code} <small class="text-muted font-weight-normal">(Kode Tiket)</small>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-warning text-dark font-weight-bold mr-1 mb-1" onclick="insertVariable('add-message-input', '{admin_name}')">
                                + {admin_name} <small class="text-muted font-weight-normal">(Nama Admin)</small>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold mb-1" onclick="insertVariable('add-message-input', '{kategori}')">
                                + {kategori} <small class="text-muted font-weight-normal">(Kategori)</small>
                            </button>
                        </div>

                        <textarea name="message" id="add-message-input" rows="4" class="form-control" placeholder="Tuliskan isi pesan balasan..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-bold text-dark">Urutan Tampilan</label>
                            <input type="number" name="order_index" class="form-control" value="0" min="0">
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
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Template -->
<div class="modal fade" id="modal-edit-template" tabindex="-1" role="dialog" aria-labelledby="modalEditTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form id="form-edit-template" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalEditTitle">
                        <i class="fas fa-edit mr-2"></i>Edit Template Chat
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-7 form-group">
                            <label class="font-weight-bold text-dark">Judul Template <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit-title" class="form-control" required maxlength="100">
                        </div>
                        <div class="col-md-5 form-group">
                            <label class="font-weight-bold text-dark">Kategori Status <span class="text-danger">*</span></label>
                            <select name="category" id="edit-category" class="form-control font-weight-bold" required>
                                <option value="on_progress">🔵 On Progress</option>
                                <option value="pending">🟡 Pending</option>
                                <option value="closed">🟢 Closed</option>
                                <option value="general">⚪ Umum / General</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center">
                            <span>Isi Pesan Template <span class="text-danger">*</span></span>
                            <small class="text-muted font-weight-normal">Klik variabel di bawah untuk menyisipkan otomatis:</small>
                        </label>
                        
                        <!-- Variable Helper Chips -->
                        <div class="mb-2 d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold mr-1 mb-1" onclick="insertVariable('edit-message-input', '{user_name}')">
                                + {user_name}
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-info font-weight-bold mr-1 mb-1" onclick="insertVariable('edit-message-input', '{nomor_laptop}')">
                                + {nomor_laptop}
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-success font-weight-bold mr-1 mb-1" onclick="insertVariable('edit-message-input', '{ticket_code}')">
                                + {ticket_code}
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-warning text-dark font-weight-bold mr-1 mb-1" onclick="insertVariable('edit-message-input', '{admin_name}')">
                                + {admin_name}
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold mb-1" onclick="insertVariable('edit-message-input', '{kategori}')">
                                + {kategori}
                            </button>
                        </div>

                        <textarea name="message" id="edit-message-input" rows="4" class="form-control" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-bold text-dark">Urutan Tampilan</label>
                            <input type="number" name="order_index" id="edit-order-index" class="form-control" min="0">
                        </div>
                        <div class="col-md-6 form-group mb-0 d-flex align-items-center pt-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input" id="edit-is-active" value="1">
                                <label class="custom-control-label font-weight-bold text-dark" for="edit-is-active">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm">
                        <i class="fas fa-save mr-1"></i> Perbarui Template
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

    function openEditModal(template) {
        const form = document.getElementById('form-edit-template');
        form.action = `/admin/chat-templates/${template.id}`;
        
        document.getElementById('edit-title').value = template.title || '';
        document.getElementById('edit-category').value = template.category || 'general';
        document.getElementById('edit-message-input').value = template.message || '';
        document.getElementById('edit-order-index').value = template.order_index ?? 0;
        document.getElementById('edit-is-active').checked = !!template.is_active;

        $('#modal-edit-template').modal('show');
    }
</script>
@stop
