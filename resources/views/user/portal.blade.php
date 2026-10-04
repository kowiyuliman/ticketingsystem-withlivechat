<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor IT - Layanan Pengaduan Kendala IT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background-color: #f8fafc;
        }
        .btn-primary-saas {
            background-color: #0284c7;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary-saas:hover:not(:disabled) {
            background-color: #0369a1;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
        .btn-primary-saas:active:not(:disabled) {
            transform: scale(0.99);
        }
        .tab-underline-active {
            color: #0284c7 !important;
            border-bottom: 2px solid #0284c7 !important;
            font-weight: 700 !important;
        }
        .tab-underline-inactive {
            color: #64748b !important;
            border-bottom: 2px solid transparent !important;
            font-weight: 500 !important;
        }
        .tab-underline-inactive:hover {
            color: #0f172a !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
        .category-card-active {
            border-color: #0284c7 !important;
            background-color: #f0f9ff !important;
            color: #0369a1 !important;
            box-shadow: 0 0 0 1px #0284c7 !important;
            font-weight: 700 !important;
        }
        .category-card-inactive {
            border-color: #e2e8f0 !important;
            background-color: #ffffff !important;
            color: #334155 !important;
        }
        .category-card-inactive:hover {
            border-color: #cbd5e1 !important;
            background-color: #f8fafc !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800 antialiased">

    {{-- 1. HEADER UTAMA --}}
    <header class="bg-white border-b border-slate-200/80 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <div class="w-9 h-9 rounded-lg bg-sky-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div>
                        <div class="font-bold text-base text-slate-900 leading-tight tracking-tight">Lapor IT</div>
                        <div class="text-xs text-slate-500 font-medium">
                            @if(!empty($detection['hostname']) && !in_array($detection['hostname'], ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN']))
                                {{ $detection['hostname'] }} &bull; {{ $detection['nama_user'] }}
                            @else
                                Portal Pengaduan Kendala IT
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-3">
                <div class="hidden sm:flex items-center space-x-2 text-xs text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                    <span class="w-2 h-2 rounded-full {{ $detection['is_detected'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span class="font-medium text-slate-700">{{ $detection['nama_user'] ?: 'Pengguna' }}</span>
                </div>
                @auth
                    <a href="{{ url('/admin/dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                        <i class="fas fa-shield-alt mr-1"></i> Dashboard Admin
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                        Login IT &rarr;
                    </a>
                @endauth
            </div>
        </div>

        {{-- 2. UNDERLINE NAVIGATION TABS (OPSI 1) --}}
        @php $activeTab = request('tab', 'create'); @endphp
        <div class="border-t border-slate-100 bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 flex space-x-8 overflow-x-auto">
                <button type="button" onclick="switchTab('create')" id="tab-create" class="py-3 px-1 text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab == 'create' ? 'tab-underline-active' : 'tab-underline-inactive' }}">
                    <span>Laporkan Kendala</span>
                </button>
                <button type="button" onclick="switchTab('history')" id="tab-history" class="py-3 px-1 text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab == 'history' ? 'tab-underline-active' : 'tab-underline-inactive' }}">
                    <span>Tiket Saya</span>
                    <span class="bg-slate-100 text-slate-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-slate-200">
                        {{ $tickets->count() }}
                    </span>
                </button>
                <button type="button" onclick="switchTab('assets')" id="tab-assets" class="py-3 px-1 text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 {{ $activeTab == 'assets' ? 'tab-underline-active' : 'tab-underline-inactive' }}">
                    <span>Perangkat Saya</span>
                    @if($myAssets->isNotEmpty())
                        <span class="bg-slate-100 text-slate-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-slate-200">
                            {{ $myAssets->count() }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </header>

    {{-- MAIN CONTAINER --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 pt-8 pb-12 flex-1 w-full">

        <!-- Floating Toast Notifications -->
        @include('partials.floating_toast')

        {{-- TAB 1: FORM PENGADUAN INSTANT --}}
        <div id="view-create" class="{{ $activeTab == 'create' ? '' : 'hidden' }} max-w-3xl mx-auto">
            
            {{-- HERO / INTRODUCTION --}}
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                    Laporkan Kendala IT
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Sampaikan kendala yang Anda alami kepada tim IT. Kami akan segera menindaklanjuti laporan Anda.
                </p>
            </div>

            {{-- INFORMASI PERANGKAT (SINGLE HORIZONTAL CONTAINER) --}}
            <div class="bg-white border border-slate-200/90 rounded-xl p-4 sm:p-5 mb-6 shadow-2xs">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 rounded-full {{ $detection['is_detected'] ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        <h2 class="text-xs font-bold text-slate-600 uppercase tracking-wider">
                            Informasi Perangkat Anda
                        </h2>
                    </div>
                    <button type="button" onclick="openLaptopModal()" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition-colors inline-flex items-center gap-1">
                        <i class="fas fa-sync-alt text-[10px]"></i> Ganti Perangkat
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-400 font-medium mb-0.5">Nomor Laptop</div>
                        <div class="text-sm font-bold text-slate-900">
                            {{ $detection['hostname'] ?: 'Belum Terdeteksi' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium mb-0.5">Nama Pemilik</div>
                        <div class="text-sm font-bold text-slate-900 truncate" title="{{ $detection['nama_user'] }}">
                            {{ $detection['nama_user'] ?: '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-medium mb-0.5">IP Address</div>
                        <div class="text-sm font-bold text-slate-900 font-mono">
                            {{ $detection['ip_address'] ?: '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM UTAMA PENGADUAN --}}
            <div class="bg-white border border-slate-200/90 rounded-2xl p-6 sm:p-8 shadow-sm">
                <form action="{{ route('ticket.store') }}" method="POST" id="main-ticket-form" onsubmit="return handleFormSubmit(this)">
                    @csrf
                    
                    <input type="hidden" name="nomor_laptop" value="{{ $detection['hostname'] }}">
                    <input type="hidden" name="ip_address" value="{{ $detection['ip_address'] }}">
                    <input type="hidden" name="nama" value="{{ $detection['nama_user'] }}">

                    {{-- KATEGORI KENDALA --}}
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-900 mb-1">
                            Kategori Kendala <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500 mb-3">
                            Pilih kategori yang sesuai dengan kendala yang Anda alami.
                        </p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="category-container">
                            {{-- HARDWARE --}}
                            <label onclick="selectCategory(this)" class="kat-card category-card-inactive border rounded-xl p-3.5 text-center cursor-pointer transition-all flex items-center justify-center">
                                <input type="radio" name="kategori" value="hardware" class="sr-only">
                                <span class="text-sm font-medium kat-label">Hardware</span>
                            </label>
                            
                            {{-- SOFTWARE --}}
                            <label onclick="selectCategory(this)" class="kat-card category-card-inactive border rounded-xl p-3.5 text-center cursor-pointer transition-all flex items-center justify-center">
                                <input type="radio" name="kategori" value="software" class="sr-only">
                                <span class="text-sm font-medium kat-label">Software</span>
                            </label>
                            
                            {{-- NETWORK --}}
                            <label onclick="selectCategory(this)" class="kat-card category-card-inactive border rounded-xl p-3.5 text-center cursor-pointer transition-all flex items-center justify-center">
                                <input type="radio" name="kategori" value="network" class="sr-only">
                                <span class="text-sm font-medium kat-label">Network</span>
                            </label>
                            
                            {{-- LAINNYA --}}
                            <label onclick="selectCategory(this)" class="kat-card category-card-inactive border rounded-xl p-3.5 text-center cursor-pointer transition-all flex items-center justify-center">
                                <input type="radio" name="kategori" value="other" class="sr-only">
                                <span class="text-sm font-medium kat-label">Lainnya</span>
                            </label>
                        </div>
                    </div>

                    {{-- DESKRIPSI KENDALA --}}
                    <div class="mb-6">
                        <label for="ticket-deskripsi" class="block text-sm font-bold text-slate-900 mb-1">
                            Deskripsi Kendala <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500 mb-2.5">
                            Jelaskan secara detail kendala yang Anda alami. Semakin jelas informasinya, semakin cepat kami membantu.
                        </p>
                        
                        <textarea 
                            name="deskripsi" 
                            id="ticket-deskripsi" 
                            rows="5" 
                            maxlength="1000"
                            required 
                            class="w-full p-4 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 transition-all outline-none text-slate-800 text-sm placeholder:text-slate-400" 
                            placeholder="Contoh: Laptop tidak dapat terhubung ke jaringan Wi-Fi, muncul pesan error saat membuka aplikasi, atau kendala lainnya..."></textarea>
                        
                        <div class="flex justify-end mt-1.5 text-xs text-slate-400">
                            <span id="char-counter">0</span>/1000
                        </div>
                    </div>

                    {{-- SUBMIT BUTTON (SATU TOMBOL SAJA) --}}
                    <button type="submit" id="btn-submit" disabled class="w-full btn-primary-saas text-white py-3.5 px-6 rounded-xl font-semibold text-sm tracking-wide transition-all flex items-center justify-center space-x-2 opacity-50 cursor-not-allowed">
                        <span id="btn-text">Kirim Laporan</span>
                        <span id="btn-spinner" class="hidden">
                            <i class="fas fa-circle-notch fa-spin mr-2"></i> Mengirim Laporan...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- TAB 2: TIKET SAYA & STATUS --}}
        <div id="view-history" class="{{ $activeTab == 'history' ? '' : 'hidden' }} max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/90">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Riwayat Pengaduan Saya</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar tiket yang terdaftar untuk laptop {{ $detection['hostname'] }}</p>
                    </div>
                    <button type="button" onclick="switchTab('create')" class="btn-primary-saas text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-2xs transition-all flex items-center gap-1.5">
                        <i class="fas fa-plus text-[10px]"></i> Buat Laporan Baru
                    </button>
                </div>

                @if($tickets->isEmpty())
                    <div class="text-center py-12 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                        <i class="fas fa-clipboard-check text-3xl text-slate-300 mb-2 block"></i>
                        <p class="text-sm font-semibold text-slate-700 mb-1">Belum Ada Pengaduan</p>
                        <p class="text-xs text-slate-400 mb-4">Semua sistem laptop {{ $detection['hostname'] }} berjalan normal tanpa kendala aktif.</p>
                        <button type="button" onclick="switchTab('create')" class="btn-primary-saas text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-2xs transition-all">
                            Buat Pengaduan Pertama
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($tickets as $t)
                            <a href="{{ route('ticket.show', $t->id) }}" class="block p-4 sm:p-5 rounded-xl border border-slate-200 hover:border-sky-300 bg-white hover:bg-slate-50/60 transition-all shadow-2xs group">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start space-x-3.5">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 group-hover:bg-sky-50 text-slate-600 group-hover:text-sky-600 flex items-center justify-center font-bold text-sm flex-shrink-0 transition-colors">
                                            @if($t->kategori == 'hardware') <i class="fas fa-microchip"></i>
                                            @elseif($t->kategori == 'software') <i class="fas fa-code"></i>
                                            @elseif($t->kategori == 'network') <i class="fas fa-wifi"></i>
                                            @else <i class="fas fa-question"></i> @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-xs font-bold text-sky-800">#{{ $t->ticket_code }}</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wide
                                                    @if($t->status == 'open') bg-rose-100 text-rose-800
                                                    @elseif($t->status == 'on_progress') bg-blue-100 text-blue-800
                                                    @elseif($t->status == 'pending') bg-amber-100 text-amber-800
                                                    @elseif($t->status == 'closed') bg-emerald-100 text-emerald-800
                                                    @else bg-slate-100 text-slate-700 @endif">
                                                    {{ str_replace('_', ' ', $t->status) }}
                                                </span>
                                            </div>
                                            <h3 class="font-semibold text-sm text-slate-900 leading-snug">{{ $t->deskripsi }}</h3>
                                            <p class="text-xs text-slate-400 mt-1">
                                                Dibuat: {{ $t->created_at->format('d M Y, H:i') }}
                                                @if($t->technician) &bull; Teknisi: <span class="font-medium text-slate-700">{{ $t->technician->name }}</span> @endif
                                            </p>
                                            @if(($t->status == 'pending' || $t->status == 'cancelled') && $t->reason_text)
                                                <div class="mt-2 text-xs p-2 rounded-lg {{ $t->status == 'pending' ? 'bg-amber-50 text-amber-900 border border-amber-200' : 'bg-slate-100 text-slate-800 border border-slate-200' }}">
                                                    <span class="font-semibold">Keterangan {{ ucfirst($t->status) }}:</span> {{ $t->reason_text }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end text-xs text-sky-600 font-semibold group-hover:text-sky-800">
                                        Buka Chat &rarr;
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 3: PERANGKAT SAYA (ASSETS INVENTORY) --}}
        <div id="view-assets" class="{{ $activeTab == 'assets' ? '' : 'hidden' }} max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-slate-200/90">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Perangkat &amp; Aset IT Terdaftar</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh perlengkapan IT yang terikat pada <strong>{{ $detection['nama_user'] }}</strong> ({{ $detection['hostname'] }})</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-slate-50 text-slate-700 font-semibold px-3 py-1.5 rounded-lg border border-slate-200 flex items-center gap-1.5">
                            <span>Total Aset:</span>
                            <span class="bg-sky-600 text-white text-[11px] px-2 py-0.2 rounded-full font-bold">{{ $myAssets->count() }}</span>
                        </span>
                    </div>
                </div>

                @if($myAssets->isEmpty())
                    <div class="p-8 rounded-xl border border-dashed border-slate-200 bg-slate-50/40 text-center">
                        <i class="fas fa-laptop text-3xl text-slate-300 mb-2 block"></i>
                        <h3 class="font-semibold text-sm text-slate-800 mb-1">Laptop Aktif Terdeteksi</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mb-3">Belum ada periferal tambahan (seperti mouse, headset, LAN adapter, USB audio) yang terdaftar atas nama pengguna ini di database inventaris.</p>
                        <div class="inline-flex flex-wrap items-center justify-center gap-2 bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 font-mono">
                            <span>{{ $detection['hostname'] }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span>{{ $detection['nama_user'] }}</span>
                            <span class="text-slate-300">&bull;</span>
                            <span>{{ $detection['ip_address'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        @foreach($myAssets as $asset)
                            @php
                                $assetType = $asset->jenis ?? $asset->type_label ?? $asset->type ?? 'Aset IT';
                                $assetBrand = $asset->merk ?? $asset->brand ?? '-';
                                $assetSn = $asset->sn ?? $asset->serial_number ?? $asset->asset_code ?? '-';
                                $assetCondition = strtolower($asset->kondisi ?? $asset->condition ?? 'baik');
                                $assetStatus = strtolower($asset->status ?? 'digunakan');
                            @endphp
                            <div class="p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50/50 transition-all flex items-start space-x-3.5">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-base font-bold flex-shrink-0 border border-slate-200/60">
                                    @if(str_contains(strtolower($assetType), 'laptop')) <i class="fas fa-laptop"></i>
                                    @elseif(str_contains(strtolower($assetType), 'charger')) <i class="fas fa-plug"></i>
                                    @elseif(str_contains(strtolower($assetType), 'mouse')) <i class="fas fa-mouse"></i>
                                    @elseif(str_contains(strtolower($assetType), 'headset')) <i class="fas fa-headphones"></i>
                                    @elseif(str_contains(strtolower($assetType), 'lan')) <i class="fas fa-network-wired"></i>
                                    @else <i class="fas fa-box"></i> @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded uppercase tracking-wider border border-sky-100">
                                            {{ $assetType }}
                                        </span>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ in_array($assetCondition, ['baik', 'good', 'bagus']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                            {{ ucfirst($asset->kondisi ?? $asset->condition ?? 'Baik') }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-sm text-slate-900 truncate">
                                        {{ $assetBrand }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">
                                        S/N: <span class="font-semibold text-slate-700">{{ $assetSn }}</span>
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </main>

    {{-- MODAL GANTI / PILIH LAPTOP --}}
    <div id="laptop-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 transition-all" onclick="closeLaptopModal(event)">
        <div class="relative max-w-lg w-full bg-white rounded-2xl overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[85vh]" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Pilih / Ganti Perangkat Laptop</h3>
                    <p class="text-xs text-slate-500">Sesuaikan nomor laptop dengan database inventaris</p>
                </div>
                <button type="button" onclick="closeLaptopModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-all text-xs font-bold">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4">
                <!-- Custom Input Option -->
                <form action="{{ route('laptop.set') }}" method="POST" class="space-y-2" onsubmit="return handleSetLaptopSubmit(this)">
                    @csrf
                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Ketik Nomor Laptop</label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 flex items-center bg-slate-50 border border-slate-200 rounded-xl overflow-hidden focus-within:border-sky-500 focus-within:bg-white transition-all">
                            <span class="bg-slate-100 text-slate-700 font-bold text-xs sm:text-sm px-3.5 py-2.5 border-r border-slate-200 select-none">
                                LAP-
                            </span>
                            <input type="text" id="modal-lap-number" placeholder="Contoh: 0253" class="flex-1 bg-transparent px-3 py-2.5 text-xs sm:text-sm font-bold text-slate-800 focus:outline-none uppercase" autocomplete="off" required>
                            <input type="hidden" name="nomor_laptop" id="modal-full-laptop-sn">
                        </div>
                        <button type="submit" class="btn-primary-saas text-white px-5 py-2.5 rounded-xl text-xs font-semibold transition-all shadow-2xs">
                            Setel
                        </button>
                    </div>
                </form>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] text-slate-400 uppercase font-semibold">Atau Cari Dari Database</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- Live Search Box for Inventories -->
                <div>
                    <input type="text" id="laptop-search-input" onkeyup="filterLaptopList()" placeholder="Ketik nama pengguna atau nomor laptop..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
                </div>

                <!-- Laptop List Grid -->
                <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1" id="laptop-item-list">
                    @if(isset($availableLaptops))
                        @foreach($availableLaptops as $inv)
                            <form action="{{ route('laptop.set') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tab" value="{{ $activeTab }}">
                                <input type="hidden" name="nomor_laptop" value="{{ $inv->sn }}">
                                <button type="submit" class="w-full text-left p-2.5 rounded-xl hover:bg-sky-50 border border-transparent hover:border-sky-200 transition-all flex items-center justify-between group laptop-item-card" data-search="{{ strtolower($inv->sn . ' ' . $inv->pengguna . ' ' . $inv->department) }}">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            <i class="fas fa-laptop text-[11px]"></i>
                                        </div>
                                        <div>
                                            <span class="font-bold text-xs text-slate-800 group-hover:text-sky-700 block">{{ $inv->sn }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $inv->pengguna ?: 'Tanpa Pengguna' }} ({{ $inv->department ?: '-' }})</span>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-sky-600 font-bold opacity-0 group-hover:opacity-100 transition-opacity">Pilih &rarr;</span>
                                </button>
                            </form>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- FOOTER MPTB --}}
    @include('layouts.footer')

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        function handleSetLaptopSubmit(form) {
            const numInput = document.getElementById('modal-lap-number');
            const fullInput = document.getElementById('modal-full-laptop-sn');
            if (!numInput || !numInput.value.trim()) {
                alert('Silakan masukkan nomor laptop Anda.');
                return false;
            }
            let val = numInput.value.trim().toUpperCase();
            if (val.startsWith('LAP-')) {
                fullInput.value = val;
            } else if (val.startsWith('LAP')) {
                fullInput.value = 'LAP-' + val.substring(3).replace(/^[-\s]+/, '');
            } else {
                fullInput.value = 'LAP-' + val;
            }
            return true;
        }

        function openLaptopModal() {
            const modal = document.getElementById('laptop-modal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeLaptopModal(e) {
            if (!e || e.target.id === 'laptop-modal' || e.target.closest('button')) {
                const modal = document.getElementById('laptop-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }
        }

        function filterLaptopList() {
            const query = document.getElementById('laptop-search-input').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.laptop-item-card');
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search') || '';
                if (searchData.includes(query)) {
                    card.parentElement.classList.remove('hidden');
                } else {
                    card.parentElement.classList.add('hidden');
                }
            });
        }

        function validateForm() {
            const hasCategory = document.querySelector('input[name="kategori"]:checked');
            const descEl = document.getElementById('ticket-deskripsi');
            const charCounter = document.getElementById('char-counter');
            
            if (descEl && charCounter) {
                charCounter.innerText = descEl.value.length;
            }

            const hasDesc = descEl && descEl.value.trim().length >= 5;
            const btn = document.getElementById('btn-submit');
            if (!btn) return;

            if (hasCategory && hasDesc) {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        function selectCategory(label) {
            document.querySelectorAll('.kat-card').forEach(card => {
                card.classList.remove('category-card-active');
                card.classList.add('category-card-inactive');
            });
            
            label.classList.remove('category-card-inactive');
            label.classList.add('category-card-active');
            
            const radio = label.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
            validateForm();
        }

        function handleFormSubmit(form) {
            const laptopInput = form.querySelector('input[name="nomor_laptop"]');
            const laptopVal = laptopInput ? laptopInput.value.trim() : '';
            if (!laptopVal || laptopVal === 'BELUM TERDETEKSI' || laptopVal === 'BELUM DIPILIH' || laptopVal === 'LAP-UNKNOWN') {
                alert('Silakan pilih nomor laptop Anda terlebih dahulu.');
                openLaptopModal();
                return false;
            }
            const hasCategory = document.querySelector('input[name="kategori"]:checked');
            const descEl = document.getElementById('ticket-deskripsi');
            if (!hasCategory) {
                alert('Silakan pilih salah satu kategori kendala terlebih dahulu.');
                return false;
            }
            if (!descEl || descEl.value.trim().length < 5) {
                alert('Mohon isi deskripsi kendala minimal 5 karakter.');
                descEl?.focus();
                return false;
            }

            const btn = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');
            
            if (btnText && btnSpinner) {
                btnText.classList.add('hidden');
                btnSpinner.classList.remove('hidden');
            }
            if (btn) {
                setTimeout(() => {
                    btn.disabled = true;
                }, 50);
            }
            return true;
        }

        function switchTab(tab) {
            const views = ['create', 'history', 'assets'];
            views.forEach(v => {
                const el = document.getElementById('view-' + v);
                const btn = document.getElementById('tab-' + v);
                if (v === tab) {
                    if (el) el.classList.remove('hidden');
                    if (btn) {
                        btn.className = "py-3 px-1 text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 tab-underline-active";
                    }
                } else {
                    if (el) el.classList.add('hidden');
                    if (btn) {
                        btn.className = "py-3 px-1 text-xs sm:text-sm transition-all whitespace-nowrap flex items-center gap-2 tab-underline-inactive";
                    }
                }
            });

            if (window.history && window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.toString());
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if($detection['is_detected'] && !empty($detection['hostname']) && !in_array($detection['hostname'], ['BELUM DIPILIH', 'BELUM TERDETEKSI', 'LAP-UNKNOWN']))
                try {
                    localStorage.setItem('mptb_laptop_sn', '{{ $detection['hostname'] }}');
                } catch (e) {}
            @else
                try {
                    const localSn = localStorage.getItem('mptb_laptop_sn');
                    if (localSn && localSn.trim() !== '') {
                        const currentUrl = new URL(window.location.href);
                        if (!currentUrl.searchParams.has('laptop_sn')) {
                            currentUrl.searchParams.set('laptop_sn', localSn.trim());
                            window.location.replace(currentUrl.toString());
                            return;
                        }
                    }
                } catch (e) {}
            @endif

            const descEl = document.getElementById('ticket-deskripsi');
            if (descEl) {
                descEl.addEventListener('input', validateForm);
                descEl.addEventListener('change', validateForm);
            }
            validateForm();

            @if(session('error') || (!$detection['is_detected'] && (empty($detection['hostname']) || in_array($detection['hostname'], ['BELUM TERDETEKSI', 'BELUM DIPILIH', 'LAP-UNKNOWN']))))
                setTimeout(function() {
                    openLaptopModal();
                }, 350);
            @endif
        });
    </script>
</body>
</html>
