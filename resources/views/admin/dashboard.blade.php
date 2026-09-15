@extends('adminlte::page')

@section('title', 'Dashboard IT Support')

@section('content_header')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h1 class="m-0 font-weight-bold text-dark">Dashboard IT Support</h1>
        <p class="text-muted text-sm mb-0">Overview performa penanganan tiket & statistik keluhan IT</p>
    </div>
</div>
@stop

@section('content')

@include('partials.floating_toast')

    {{-- STATISTIK TOP CARDS (KLIK UNTUK MENUJU HALAMAN TERKAIT) --}}
    <div class="row">
        @php
            $isManagement = auth()->user()->role === 'management';
            $cards = [
                ['id'=>'card-open', 'title'=>'Open Tiket','value'=>$open,'color'=>'warning','icon'=>'fas fa-folder-open','link'=> $isManagement ? 'javascript:void(0)' : url('admin/tickets?tab=open')],
                ['id'=>'card-on-progress', 'title'=>'On Progress Ticket','value'=>$progress,'color'=>'primary','icon'=>'fas fa-spinner','link'=> $isManagement ? 'javascript:void(0)' : url('admin/tickets?tab=progress')],
                ['id'=>'card-pending', 'title'=>'Pending Tiket','value'=>$pending,'color'=>'danger','icon'=>'fas fa-pause-circle','link'=> $isManagement ? 'javascript:void(0)' : url('admin/tickets?tab=pending')],
                ['id'=>'card-closed', 'title'=>'Close Tiket','value'=>$closed,'color'=>'success','icon'=>'fas fa-check-circle','link'=> $isManagement ? 'javascript:void(0)' : url('admin/tickets?tab=closed')],
                ['id'=>'card-cancelled', 'title'=>'Cancel Tiket','value'=>$cancelled,'color'=>'secondary','icon'=>'fas fa-ban','link'=> $isManagement ? 'javascript:void(0)' : url('admin/tickets?tab=cancel')],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="col-xl col-lg-4 col-md-6 col-12 mb-3">
            <div class="small-box bg-{{ $card['color'] }} shadow-sm h-100 d-flex flex-column justify-content-center hover-card py-2" 
                 @if(!$isManagement) onclick="window.location='{{ $card['link'] }}'" style="cursor: pointer; min-height: 95px; border-radius: 10px;" @else style="min-height: 95px; border-radius: 10px;" @endif>
                <div class="inner">
                    <h3 id="{{ $card['id'] }}" class="font-weight-bold mb-1">
                        {{ $card['value'] }}
                    </h3>
                    <p class="mb-0 font-weight-bold" style="font-size: 13px;">{{ $card['title'] }}</p>
                </div>
                <div class="icon">
                    <i class="{{ $card['icon'] }}"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- LATEST OPEN TICKETS (PERLU PERHATIAN SEGERA) --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header border-0 d-flex justify-content-between align-items-center w-100">
                    <h3 class="card-title font-weight-bold text-dark mb-0" style="float: none;">
                        <i class="fas fa-exclamation-circle text-warning mr-1"></i> Tiket Open Terbaru (Perlu Ditangani)
                    </h3>
                    @if(!$isManagement)
                    <div class="card-tools ml-auto">
                        <a href="{{ url('admin/tickets') }}" class="btn btn-xs btn-outline-warning font-weight-bold">
                            Lihat Semua Tiket &rarr;
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Kode Tiket</th>
                                <th>Pengirim / Laptop</th>
                                <th>Kategori</th>
                                <th>Kendala</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="latest-open-tickets-tbody">
                            @forelse($latestOpenTickets as $t)
                            <tr>
                                <td>
                                    <span class="badge badge-light border font-weight-bold">{{ $t->ticket_code }}</span>
                                </td>
                                <td>
                                    <b>{{ $t->nama }}</b>
                                    <div class="text-muted text-xs">💻 {{ $t->nomor_laptop }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-warning uppercase font-weight-bold">{{ strtoupper($t->kategori ?? 'General') }}</span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $t->deskripsi }}">
                                        {{ $t->deskripsi }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if(!$isManagement)
                                    <a href="{{ url('admin/ticket/show/' . $t->id) }}" class="btn btn-xs btn-primary font-weight-bold shadow-2xs">
                                        <i class="fas fa-eye mr-1"></i> Detail
                                    </a>
                                    @else
                                    <span class="badge badge-light border text-muted px-2 py-1">
                                        <i class="fas fa-eye mr-1"></i> Monitor
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-open-tickets-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle text-success mr-1"></i> Tidak ada tiket Open yang belum ditangani!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTIK TIKET PER LAPTOP & PENGGUNA (TEPAT DI BAWAH TIKET OPEN) --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-laptop text-info mr-1"></i> Statistik Tiket per Laptop & Pengguna (Database Inventories)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped hover" id="laptopTable">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nomor Laptop (SN)</th>
                                    <th>Nama Pengguna (Inventories)</th>
                                    <th>Departemen</th>
                                    <th class="text-center">Total Tiket</th>
                                    <th class="text-center">Open</th>
                                    <th class="text-center">Progress</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Closed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laptopStats as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td><span class="badge badge-info px-2.5 py-1">💻 {{ $item['nomor_laptop'] }}</span></td>
                                    <td><b>{{ $item['pengguna'] }}</b></td>
                                    <td>{{ $item['department'] }}</td>
                                    <td class="text-center"><b>{{ $item['total_ticket'] }}</b></td>
                                    <td class="text-center">
                                        <span class="badge badge-danger px-2 py-1">
                                            {{ $item['open'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary px-2 py-1">
                                            {{ $item['progress'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning text-dark px-2 py-1">
                                            {{ $item['pending'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success px-2 py-1">
                                            {{ $item['closed'] }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART ROW 1 --}}
    <div class="row mt-2">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-line text-primary mr-1"></i> Tren Tiket Harian
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12 mb-3">
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-bar text-success mr-1"></i> Grafik Tiket Bulanan
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHART ROW 2 --}}
    <div class="row">
        <div class="col-lg-6 col-12 mb-3">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-chart-pie text-warning mr-1"></i> Distribusi Kategori Kendala
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kategoriChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-12 mb-3">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users-cog text-info mr-1"></i> Grafik Workload IT
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- WORKLOAD IT (PALING BAWAH) --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card card-outline card-dark shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-user-shield text-info mr-1"></i> Workload IT
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>IT</th>
                                <th class="text-center">Beban Tiket</th>
                            </tr>
                        </thead>
                        <tbody id="workload-table-tbody">
                            @forelse($technicianWorkload as $tech)
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle text-secondary mr-1"></i>
                                    <b>{{ $tech->technician->name ?? ($tech->technician->username ?? 'IT') }}</b>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                        {{ $tech->total_ticket }} Tiket
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted py-4">Belum ada penugasan tiket.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<audio id="notifSound" preload="auto">
    <source src="/sound/notification.mp3" type="audio/mpeg">
</audio>
@include('layouts.footer')
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
    let dailyChart;
    let monthlyChart;
    let kategoriChart;
    let workloadChart;
    let lastTicketId = {{ $latestOpenTickets->first()?->id ?? 0 }};

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    $(document).ready(function () {

        // DATATABLE INIT
        $('#laptopTable').DataTable({
            responsive: true,
            autoWidth: false,
            lengthMenu: [5, 10, 25, 50],
            order: [[4, 'desc']],
            columnDefs: [
                { targets: [0, 4, 5, 6, 7, 8], className: 'text-center' }
            ]
        });

        // DAILY CHART (14-Day Continuous Line)
        dailyChart = new Chart(
            document.getElementById('dailyChart'),
            {
                type: 'line',
                data: {
                    labels: {!! json_encode($dailyLabels) !!},
                    datasets: [{
                        label: 'Tiket Harian',
                        data: {!! json_encode($dailyValues) !!},
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.15)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ' ' + ctx.parsed.y + ' Tiket';
                                }
                            }
                        }
                    }
                }
            }
        );

        // MONTHLY CHART
        monthlyChart = new Chart(
            document.getElementById('monthlyChart'),
            {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Tiket Bulanan',
                        data: {!! json_encode($monthlyValues) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 }
                        }
                    }
                }
            }
        );

        // KATEGORI CHART
        kategoriChart = new Chart(
            document.getElementById('kategoriChart'),
            {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($kategoriLabels) !!},
                    datasets: [{
                        data: {!! json_encode($kategoriValues) !!},
                        backgroundColor: [
                            '#0284c7', // Hardware
                            '#f59e0b', // Software
                            '#10b981', // Network
                            '#64748b'  // Other
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            }
        );

        // WORKLOAD CHART
        workloadChart = new Chart(
            document.getElementById('workloadChart'),
            {
                type: 'bar',
                data: {
                    labels: {!! json_encode($technicianWorkload->map(fn($t) => $t->technician?->name ?? 'IT')) !!},
                    datasets: [{
                        label: 'Jumlah Tiket',
                        data: {!! json_encode($technicianWorkload->pluck('total_ticket')) !!},
                        backgroundColor: '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 }
                        }
                    }
                }
            }
        );

        // NOTIFICATION PERMISSION
        if (Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // AUDIO UNLOCK ON FIRST CLICK
        let audioUnlocked = false;
        document.addEventListener('click', function () {
            if (audioUnlocked) return;
            const audio = document.getElementById('notifSound');
            if (audio) {
                audio.play().then(() => {
                    audio.pause();
                    audio.currentTime = 0;
                    audioUnlocked = true;
                }).catch(err => console.log(err));
            }
        }, { once: true });

        // START REALTIME POLLING (EVERY 5 SECONDS)
        loadDashboardRealtime();
        setInterval(loadDashboardRealtime, 5000);

        // REALTIME DASHBOARD UPDATE FUNCTION
        async function loadDashboardRealtime() {
            try {
                const response = await fetch('/admin/dashboard/realtime');
                if (!response.ok) return;
                const data = await response.json();

                // 1. Update Top Metric Cards
                if (document.getElementById('card-total-ticket')) document.getElementById('card-total-ticket').innerText = data.total;
                if (document.getElementById('card-open')) document.getElementById('card-open').innerText = data.open;
                if (document.getElementById('card-on-progress')) document.getElementById('card-on-progress').innerText = data.progress;
                if (document.getElementById('card-pending')) document.getElementById('card-pending').innerText = data.pending;
                if (document.getElementById('card-closed')) document.getElementById('card-closed').innerText = data.closed;
                if (document.getElementById('card-cancelled')) document.getElementById('card-cancelled').innerText = data.cancelled;

                // 2. Update Charts Seamlessly
                if (dailyChart && data.daily_labels && data.daily_values) {
                    dailyChart.data.labels = data.daily_labels;
                    dailyChart.data.datasets[0].data = data.daily_values;
                    dailyChart.update('none');
                }

                if (monthlyChart && data.monthly_labels && data.monthly_values) {
                    monthlyChart.data.labels = data.monthly_labels;
                    monthlyChart.data.datasets[0].data = data.monthly_values;
                    monthlyChart.update('none');
                }

                if (kategoriChart && data.kategori_labels && data.kategori_values) {
                    kategoriChart.data.labels = data.kategori_labels;
                    kategoriChart.data.datasets[0].data = data.kategori_values;
                    kategoriChart.update('none');
                }

                if (workloadChart && data.workload_labels && data.workload_values) {
                    workloadChart.data.labels = data.workload_labels;
                    workloadChart.data.datasets[0].data = data.workload_values;
                    workloadChart.update('none');
                }

                // 3. Update Latest Open Tickets Table
                const openTbody = document.getElementById('latest-open-tickets-tbody');
                if (openTbody && data.latest_open_tickets) {
                    if (data.latest_open_tickets.length === 0) {
                        openTbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle text-success mr-1"></i> Tidak ada tiket Open yang belum ditangani!
                                </td>
                            </tr>
                        `;
                    } else {
                        let html = '';
                        data.latest_open_tickets.forEach(t => {
                            html += `
                                <tr>
                                    <td><span class="badge badge-light border font-weight-bold">${escapeHtml(t.ticket_code)}</span></td>
                                    <td>
                                        <b>${escapeHtml(t.nama)}</b>
                                        <div class="text-muted text-xs">💻 ${escapeHtml(t.nomor_laptop)}</div>
                                    </td>
                                    <td><span class="badge badge-warning uppercase font-weight-bold">${escapeHtml(t.kategori)}</span></td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 180px;" title="${escapeHtml(t.deskripsi)}">${escapeHtml(t.deskripsi)}</span></td>
                                    <td class="text-center">
                                        @if(!$isManagement)
                                        <a href="${t.show_url}" class="btn btn-xs btn-primary font-weight-bold shadow-2xs">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>
                                        @else
                                        <span class="badge badge-light border text-muted px-2 py-1">
                                            <i class="fas fa-eye mr-1"></i> Monitor
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                            `;
                        });
                        openTbody.innerHTML = html;
                    }
                }

                // 4. Update Workload IT Table
                const workloadTbody = document.getElementById('workload-table-tbody');
                if (workloadTbody && data.workload_table) {
                    if (data.workload_table.length === 0) {
                        workloadTbody.innerHTML = `<tr><td colspan="2" class="text-center text-muted py-4">Belum ada penugasan tiket.</td></tr>`;
                    } else {
                        let html = '';
                        data.workload_table.forEach(tech => {
                            html += `
                                <tr>
                                    <td>
                                        <i class="fas fa-user-circle text-secondary mr-1"></i>
                                        <b>${escapeHtml(tech.name)}</b>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-primary px-3 py-1 font-weight-bold">
                                            ${tech.total} Tiket
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });
                        workloadTbody.innerHTML = html;
                    }
                }

                // 5. Trigger Sound & Desktop Notification on New Ticket
                if (data.latest_ticket_id && data.latest_ticket_id > lastTicketId) {
                    if (lastTicketId > 0) {
                        showNotif(data.latest_ticket_message || 'Ada tiket baru masuk!');
                    }
                    lastTicketId = data.latest_ticket_id;
                }

            } catch (error) {
                console.error('Error loading realtime dashboard data:', error);
            }
        }

        // SHOW NOTIFICATION POPUP & AUDIO
        function showNotif(message) {
            const audio = document.getElementById('notifSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(err => console.log('Audio playback blocked until user interacts', err));
            }

            if (Notification.permission === 'granted') {
                new Notification('Tiket Baru Masuk!', {
                    body: message,
                    icon: '/favicon.ico',
                    requireInteraction: true
                });
            }

            let notif = document.createElement('div');
            notif.className = 'ticket-popup';
            notif.innerHTML = `
                <div style="font-weight:bold; color: #0284c7; display: flex; align-items: center; justify-content: space-between;">
                    <span>📢 Tiket Baru Masuk!</span>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-weight:bold;">✕</button>
                </div>
                <div style="margin-top:6px; font-size: 13px; color: #334155;">
                    ${escapeHtml(message)}
                </div>
            `;

            document.body.appendChild(notif);
            setTimeout(() => { if (notif.parentNode) notif.remove(); }, 7000);
        }
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
    .hover-card {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.16) !important;
    }

    .ticket-popup {
        position: fixed;
        top: 20px;
        right: 20px;
        width: 320px;
        background: #ffffff;
        border-left: 5px solid #0284c7;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,.2);
        z-index: 999999;
        animation: slideIn .4s ease;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity:0; }
        to { transform: translateX(0); opacity:1; }
    }

    .chart-container {
        position: relative;
        height: 320px;
    }

    .small-box {
        border-radius: 12px;
        overflow: hidden;
    }

    .small-box .inner h3 {
        font-size: 28px;
        font-weight: bold;
    }

    .card {
        border-radius: 12px;
    }

    .table-responsive {
        overflow-x: auto;
    }
</style>
@stop