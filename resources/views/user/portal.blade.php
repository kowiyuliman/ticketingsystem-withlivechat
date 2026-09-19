<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor IT Portal - MPTB Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .sky-gradient-bg {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
        }
        .sky-btn {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 14px 0 rgba(2, 132, 199, 0.35);
        }
        .sky-btn:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            box-shadow: 0 6px 20px 0 rgba(2, 132, 199, 0.45);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen pb-16">

    <!-- Navbar -->
    <header class="bg-white border-b border-sky-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('login') }}" class="flex items-center space-x-3 hover:opacity-90 transition-opacity" title="Login Admin">
                    <div class="w-10 h-10 rounded-xl bg-sky-500 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-sky-200">
                        🛠️
                    </div>
                    <div>
                        <h1 class="font-bold text-lg text-slate-900 tracking-tight leading-none">Lapor IT</h1>
                        <p class="text-xs text-sky-600 font-medium">
                            @if(!empty($detection['hostname']))
                                {{ $detection['hostname'] }} • {{ $detection['nama_user'] }}
                            @else
                                Portal Pengaduan Kendala IT
                            @endif
                        </p>
                    </div>
                </a>
            </div>

            <!-- <div class="flex items-center space-x-2">
                <button type="button" onclick="openLaptopModal()" class="bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                    <span>💻</span>
                    <span>{{ $detection['hostname'] ?: 'Pilih Laptop' }}</span>
                </button> -->
                @auth
                    <a href="{{ url('/admin/dashboard') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold transition-all">
                        Dashboard Admin
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-6">

        <!-- Floating Toast Notifications -->
        @include('partials.floating_toast')

        <!-- Navigation Tabs -->
        @php $activeTab = request('tab', 'create'); @endphp
        <div class="flex justify-center mb-6">
            <div class="bg-sky-100/70 p-1.5 rounded-2xl flex items-center justify-center space-x-1 sm:space-x-2 border border-sky-200/60 overflow-x-auto w-full sm:w-auto max-w-xl">
                <button onclick="switchTab('create')" id="tab-create" class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab == 'create' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-sky-700' }} flex items-center justify-center gap-2">
                    <span>📝 Buat Laporan</span>
                </button>
                <button onclick="switchTab('history')" id="tab-history" class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab == 'history' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-sky-700' }} flex items-center justify-center gap-2">
                    <span>📋 Tiket Saya</span>
                    <span class="bg-sky-200 text-sky-800 text-[10px] px-2 py-0.5 rounded-full">{{ $tickets->count() }}</span>
                </button>
                <button onclick="switchTab('assets')" id="tab-assets" class="flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all {{ $activeTab == 'assets' ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:text-sky-700' }} flex items-center justify-center gap-2">
                    <span>💻 Perangkat Saya</span>
                    @if($myAssets->isNotEmpty())
                        <span class="bg-sky-200 text-sky-800 text-[10px] px-2 py-0.5 rounded-full">{{ $myAssets->count() }}</span>
                    @endif
                </button>
            </div>
        </div>

        <!-- TAB 1: FORM PENGADUAN INSTANT -->
        <div id="view-create" class="{{ $activeTab == 'create' ? '' : 'hidden' }} max-w-3xl mx-auto">
            
            <div class="sky-gradient-bg rounded-3xl p-6 sm:p-8 mb-6 border border-sky-200/70 shadow-2xs relative overflow-hidden">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-1">Ada Kendala Apa Hari Ini?</h2>
                <!-- <p class="text-xs sm:text-xl text-slate-600 font-medium font-bold">Pilih kategori dan ceritakan kendala anda </p> -->
                <p class="text-xs sm:text-sm text-slate-600 font-small">Penting ojo curhat :)</p>
            </div>

            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-sky-100/50 border border-sky-100">
                
                @if($detection['is_detected'])
                    <!-- Info Summary Card (Terdeteksi Otomatis / Terpilih) -->
                    <div class="mb-6 p-4 bg-sky-50/60 rounded-2xl border border-sky-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-extrabold text-slate-700">Perangkat Terdeteksi di Database Inventaris</span>
                            </div>
                            <!-- <button type="button" onclick="openLaptopModal()" class="text-xs font-bold text-sky-600 hover:text-sky-800 bg-white hover:bg-sky-100 border border-sky-200 px-3 py-1 rounded-xl transition-all shadow-2xs">
                                ✏️ Ganti / Pilih Laptop
                            </button> -->
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-white p-3 rounded-xl border border-sky-100">
                                <span class="text-[11px] text-slate-400 font-medium block">Nomor Laptop</span>
                                <span class="font-extrabold text-slate-800 text-xs">💻 {{ $detection['hostname'] }}</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-sky-100">
                                <span class="text-[11px] text-slate-400 font-medium block">Nama Pemilik</span>
                                <span class="font-extrabold text-slate-800 text-xs">👤 {{ $detection['nama_user'] }}</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-sky-100">
                                <span class="text-[11px] text-slate-400 font-medium block">IP Address (Kabel/LAN)</span>
                                <span class="font-extrabold text-slate-800 text-xs">🌐 {{ $detection['ip_address'] }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Alert Card Jika Belum Terdeteksi Otomatis -->
                    <div class="mb-6 p-4 bg-amber-50/90 rounded-2xl border border-amber-200">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm flex-shrink-0">
                                    ⚠️
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs sm:text-sm text-amber-900">Nomor Laptop Belum Terdeteksi Otomatis</h4>
                                    <p class="text-[11px] text-amber-700">Silakan pilih nomor laptop Anda agar sistem mencocokkan dengan data pemilik & riwayat pengaduan.</p>
                                </div>
                            </div>
                            <button type="button" onclick="openLaptopModal()" class="w-full sm:w-auto bg-amber-600 hover:bg-amber-700 active:scale-95 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5 flex-shrink-0">
                                <span>🔍</span>
                                <span>Pilih Nomor Laptop</span>
                            </button>
                        </div>
                    </div>
                @endif

                <form action="{{ route('ticket.store') }}" method="POST" onsubmit="return handleFormSubmit(this)">
                    @csrf
                    
                    <input type="hidden" name="nomor_laptop" value="{{ $detection['hostname'] }}">
                    <input type="hidden" name="ip_address" value="{{ $detection['ip_address'] }}">
                    <input type="hidden" name="nama" value="{{ $detection['nama_user'] }}">

                    <!-- Category Selector -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">
                            Kategori Kendala <span class="text-rose-500">* (Wajib Pilih)</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="category-container">
                            <label onclick="selectCategory(this)" class="kat-card border border-slate-200 hover:border-sky-300 bg-white p-3 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all hover:scale-[1.02]">
                                <input type="radio" name="kategori" value="hardware" class="sr-only">
                                <span class="text-2xl mb-1">🔌</span>
                                <span class="text-xs font-bold text-slate-700 kat-label">Hardware</span>
                            </label>
                            <label onclick="selectCategory(this)" class="kat-card border border-slate-200 hover:border-sky-300 bg-white p-3 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all hover:scale-[1.02]">
                                <input type="radio" name="kategori" value="software" class="sr-only">
                                <span class="text-2xl mb-1">💻</span>
                                <span class="text-xs font-bold text-slate-700 kat-label">Software</span>
                            </label>
                            <label onclick="selectCategory(this)" class="kat-card border border-slate-200 hover:border-sky-300 bg-white p-3 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all hover:scale-[1.02]">
                                <input type="radio" name="kategori" value="network" class="sr-only">
                                <span class="text-2xl mb-1">🌐</span>
                                <span class="text-xs font-bold text-slate-700 kat-label">Network</span>
                            </label>
                            <label onclick="selectCategory(this)" class="kat-card border border-slate-200 hover:border-sky-300 bg-white p-3 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all hover:scale-[1.02]">
                                <input type="radio" name="kategori" value="other" class="sr-only">
                                <span class="text-2xl mb-1">❓</span>
                                <span class="text-xs font-bold text-slate-700 kat-label">Other</span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ceritakan Kendala Anda <span class="text-rose-500">* (Wajib Isi)</span></label>
                        <textarea name="deskripsi" id="ticket-deskripsi" rows="4" required class="w-full p-4 rounded-2xl border border-sky-200 focus:border-sky-500 focus:ring-4 focus:ring-sky-100 transition-all outline-none text-slate-800 text-sm placeholder:text-slate-400" 
                        placeholder="Contoh: Layar monitor laptop bergaris / microsip tidak bisa berdering / buka extensi chrome..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="btn-submit" disabled class="w-full sky-btn text-white py-3.5 rounded-2xl font-bold text-sm tracking-wide transition-all flex items-center justify-center space-x-2 opacity-50 cursor-not-allowed">
                        <span id="btn-text">Kirim Laporan Kendala Sekarang</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- TAB 2: TIKET SAYA & STATUS -->
        <div id="view-history" class="{{ $activeTab == 'history' ? '' : 'hidden' }} max-w-4xl mx-auto">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-sky-100/50 border border-sky-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Riwayat Pengaduan Saya</h2>
                        <p class="text-xs text-slate-500">Daftar tiket yang terdaftar untuk laptop {{ $detection['hostname'] }}</p>
                    </div>
                    <button type="button" onclick="switchTab('create')" class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                        <span>➕</span>
                        <span>Buat Tiket Baru</span>
                    </button>
                </div>

                @if($tickets->isEmpty())
                    <div class="text-center py-12 border-2 border-dashed border-sky-100 rounded-2xl bg-sky-50/20">
                        <span class="text-4xl block mb-2">📋</span>
                        <p class="text-sm font-bold text-slate-700 mb-1">Belum Ada Pengaduan</p>
                        <p class="text-xs text-slate-400 mb-4">Semua sistem laptop {{ $detection['hostname'] }} berjalan lancar.</p>
                        <button type="button" onclick="switchTab('create')" class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all">
                            Buat Pengaduan Pertama
                        </button>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($tickets as $t)
                            <a href="{{ route('ticket.show', $t->id) }}" class="block p-5 rounded-2xl border border-sky-100 hover:border-sky-300 bg-sky-50/30 hover:bg-sky-50 transition-all shadow-2xs">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-lg flex-shrink-0">
                                            @if($t->kategori == 'hardware') 🔌 @elseif($t->kategori == 'software') 💻 @elseif($t->kategori == 'network') 🌐 @else ❓ @endif
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="text-xs font-bold text-sky-800">#{{ $t->ticket_code }}</span>
                                                <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase
                                                    @if($t->status == 'open') bg-rose-100 text-rose-800
                                                    @elseif($t->status == 'on_progress') bg-amber-100 text-amber-800
                                                    @elseif($t->status == 'closed') bg-emerald-100 text-emerald-800
                                                    @else bg-slate-100 text-slate-700 @endif">
                                                    {{ str_replace('_', ' ', $t->status) }}
                                                </span>
                                            </div>
                                            <h3 class="font-bold text-sm text-slate-900 leading-snug">{{ $t->deskripsi }}</h3>
                                            <p class="text-xs text-slate-500 mt-1">
                                                Dibuat: {{ $t->created_at->format('d M Y, H:i') }}
                                                @if($t->technician) • Teknisi: <span class="font-bold text-slate-700">{{ $t->technician->name }}</span> @endif
                                            </p>
                                            @if(($t->status == 'pending' || $t->status == 'cancelled') && $t->reason_text)
                                                <div class="mt-2 text-xs p-2 rounded-xl {{ $t->status == 'pending' ? 'bg-amber-100/70 text-amber-900 border border-amber-200' : 'bg-slate-200/70 text-slate-800 border border-slate-300' }}">
                                                    <span class="font-bold">⚠️ Keterangan {{ ucfirst($t->status) }}:</span> {{ $t->reason_text }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <span class="text-xs text-sky-700 font-bold flex items-center gap-1">
                                            💬 Buka Chat & Live Status
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: PERANGKAT SAYA (ASSETS INVENTORY) -->
        <div id="view-assets" class="{{ $activeTab == 'assets' ? '' : 'hidden' }} max-w-4xl mx-auto">
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-sky-100/50 border border-sky-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-sky-50">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">Perangkat &amp; Aset IT Terdaftar</h2>
                        <p class="text-xs text-slate-500">Daftar seluruh perlengkapan IT yang terikat pada <strong>{{ $detection['nama_user'] }}</strong> ({{ $detection['hostname'] }})</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-sky-50 text-sky-800 font-bold px-3 py-1.5 rounded-xl border border-sky-200/60 flex items-center gap-1.5 shadow-2xs">
                            <span>📦 Total Aset:</span>
                            <span class="bg-sky-600 text-white text-[11px] px-2 py-0.5 rounded-full font-black">{{ $myAssets->count() }}</span>
                        </span>
                    </div>
                </div>

                @if($myAssets->isEmpty())
                    <div class="p-8 rounded-2xl border border-sky-200 bg-sky-50/40 text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center text-3xl font-bold mb-3 shadow-2xs">
                            💻
                        </div>
                        <h3 class="font-extrabold text-base text-slate-900 mb-1">Laptop Aktif Terdeteksi</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto mb-4">Belum ada periferal tambahan (seperti mouse, headset, LAN adapter, USB audio, HP root) yang terdaftar atas nama pengguna ini di database inventaris.</p>
                        <div class="inline-flex flex-wrap items-center justify-center gap-2 bg-white px-4 py-2 rounded-xl border border-sky-100 text-xs font-semibold text-slate-700 shadow-2xs">
                            <span>💻 {{ $detection['hostname'] }}</span>
                            <span class="text-slate-300">•</span>
                            <span>👤 {{ $detection['nama_user'] }}</span>
                            <span class="text-slate-300">•</span>
                            <span>🌐 {{ $detection['ip_address'] }}</span>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($myAssets as $asset)
                            @php
                                $assetType = $asset->jenis ?? $asset->type_label ?? $asset->type ?? 'Aset IT';
                                $assetBrand = $asset->merk ?? $asset->brand ?? '-';
                                $assetSn = $asset->sn ?? $asset->serial_number ?? $asset->asset_code ?? '-';
                                $assetCondition = strtolower($asset->kondisi ?? $asset->condition ?? 'baik');
                                $assetStatus = strtolower($asset->status ?? 'digunakan');
                                $icon = $asset->jenis_icon ?? '📦';
                            @endphp
                            <div class="p-4 sm:p-5 rounded-2xl border border-sky-100 hover:border-sky-300 bg-white hover:bg-sky-50/20 transition-all shadow-2xs flex items-start space-x-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-sky-100/80 text-sky-800 flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-2xs border border-sky-200/50">
                                    {{ $icon }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="text-[10px] font-extrabold text-sky-700 bg-sky-100/80 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                            {{ $assetType }}
                                        </span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ in_array($assetCondition, ['baik', 'good', 'bagus']) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ ucfirst($asset->kondisi ?? $asset->condition ?? 'Baik') }}
                                        </span>
                                    </div>
                                    <h3 class="font-extrabold text-sm text-slate-900 truncate">
                                        {{ $assetBrand }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">
                                        S/N: <span class="font-bold text-slate-700">{{ $assetSn }}</span>
                                    </p>
                                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                                        <span class="flex items-center gap-1 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full {{ in_array($assetStatus, ['aktif', 'digunakan', 'assigned', 'available']) ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            Status: <strong class="text-slate-700 capitalize">{{ $asset->status ?? 'Aktif' }}</strong>
                                        </span>
                                        @if(!empty($asset->lokasi) || !empty($asset->department))
                                            <span class="text-slate-300">•</span>
                                            <span class="truncate">{{ $asset->lokasi ?? $asset->department }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </main>

    <!-- Laptop Selection Modal -->
    <div id="laptop-modal" class="fixed inset-0 z-[100] hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-all" onclick="closeLaptopModal(event)">
        <div class="relative max-w-lg w-full bg-white rounded-3xl overflow-hidden shadow-2xl border border-sky-100 flex flex-col max-h-[85vh]" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-sky-50/80 border-b border-sky-100 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <span class="text-xl">💻</span>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Pilih Nomor Laptop Anda</h3>
                        <p class="text-[11px] text-sky-700">Pilih laptop Anda untuk memfilter riwayat tiket perangkat ini</p>
                    </div>
                </div>
                <button type="button" onclick="closeLaptopModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-all text-xs font-bold">
                    ✕
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
                        <div class="flex-1 flex items-center bg-slate-50 border border-sky-200 rounded-xl overflow-hidden focus-within:border-sky-500 focus-within:bg-white transition-all">
                            <span class="bg-sky-100 text-sky-800 font-extrabold text-xs sm:text-sm px-3.5 py-2.5 border-r border-sky-200 select-none">
                                LAP-
                            </span>
                            <input type="text" id="modal-lap-number" placeholder="Contoh: 0303" class="flex-1 bg-transparent px-3 py-2.5 text-xs sm:text-sm font-bold text-slate-800 focus:outline-none uppercase" autocomplete="off" required>
                            <input type="hidden" name="nomor_laptop" id="modal-full-laptop-sn">
                        </div>
                        <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition-all shadow-2xs">
                            Setel
                        </button>
                    </div>
                </form>

                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] text-slate-400 uppercase font-semibold">Atau Pilih Dari Database Inventaris</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <!-- Live Search Box for Inventories -->
                <div>
                    <input type="text" id="laptop-search-input" onkeyup="filterLaptopList()" placeholder="🔍 Cari nama pengguna atau nomor laptop..." class="w-full bg-slate-50 border border-sky-100 rounded-xl px-3.5 py-2 text-xs focus:outline-none focus:border-sky-400 focus:bg-white transition-all">
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
                                        <span class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs flex-shrink-0">💻</span>
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
            const hasDesc = descEl && descEl.value.trim().length >= 5;
            const btn = document.getElementById('btn-submit');
            if (!btn) return;

            if (hasCategory && hasDesc) {
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('hover:opacity-95', 'active:scale-98');
            } else {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.remove('hover:opacity-95', 'active:scale-98');
            }
        }

        function selectCategory(label) {
            document.querySelectorAll('.kat-card').forEach(card => {
                card.classList.remove('border-2', 'border-sky-500', 'bg-sky-50');
                card.classList.add('border', 'border-slate-200', 'bg-white');
                const text = card.querySelector('.kat-label');
                if (text) {
                    text.classList.remove('text-sky-800');
                    text.classList.add('text-slate-700');
                }
            });
            label.classList.remove('border', 'border-slate-200', 'bg-white');
            label.classList.add('border-2', 'border-sky-500', 'bg-sky-50');
            const activeText = label.querySelector('.kat-label');
            if (activeText) {
                activeText.classList.remove('text-slate-700');
                activeText.classList.add('text-sky-800');
            }
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
            if (btnText) {
                btnText.innerHTML = "⏳ Mengirim Pengaduan...";
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
                    if (btn) btn.className = "flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all bg-white text-sky-700 shadow-sm flex items-center justify-center gap-2";
                } else {
                    if (el) el.classList.add('hidden');
                    if (btn) btn.className = "flex-1 sm:flex-initial px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition-all text-slate-600 hover:text-sky-700 flex items-center justify-center gap-2";
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

