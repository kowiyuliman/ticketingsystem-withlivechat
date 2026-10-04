<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // BASE QUERY SESUAI ROLE
        $query = Ticket::query();

        if($user->role == 'user'){
            $query->where('user_id', $user->id);
        }

        // CACHE PER ROLE (penting)
        $cacheKey = 'dashboard_'.$user->role.'_'.$user->id;
        $stats = Cache::remember($cacheKey, 30, function() use ($query){
            return [
                'total' => (clone $query)->count(),
                'open' => (clone $query)->where('status','open')->count(),
                'progress' => (clone $query)->where('status','on_progress')->count(),
                'pending' => (clone $query)->where('status','pending')->count(),
                'closed' => (clone $query)->where('status','closed')->count(),
                'cancelled' => (clone $query)->where('status','cancelled')->count(),
            ];
        });

        // ASSIGN
        $total = $stats['total'];
        $open = $stats['open'];
        $progress = $stats['progress'];
        $pending = $stats['pending'];
        $closed = $stats['closed'];
        $cancelled = $stats['cancelled'];

        // TODAY
        $today = (clone $query)->whereDate('created_at', now())->count();

        // DAILY: Continuous 14-day series multi-line per admin
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateExpr = $isSqlite ? 'date(created_at)' : 'DATE(created_at)';
        $monthExpr = $isSqlite ? 'cast(strftime(\'%m\', created_at) as integer)' : 'MONTH(created_at)';

        $adminColors = [
            '#0284c7', // Sky Blue
            '#10b981', // Emerald
            '#f59e0b', // Amber
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#14b8a6', // Teal
            '#6366f1', // Indigo
            '#f97316', // Orange
        ];

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $currentMonth = (int)now()->format('n');
        $kategoriMonth = request()->input('kategori_month', $currentMonth);
        $workloadMonth = request()->input('workload_month', $currentMonth);

        // Fetch Admins & Technicians
        $admins = User::whereIn('role', ['admin', 'technician'])->orderBy('id')->get();
        if ($admins->isEmpty()) {
            $assignedUserIds = Ticket::whereNotNull('assigned_to')->distinct()->pluck('assigned_to');
            $admins = User::whereIn('id', $assignedUserIds)->orderBy('id')->get();
        }

        $adminColorLookup = [];
        foreach ($admins as $index => $adm) {
            $adminColorLookup[$adm->id] = $adminColors[$index % count($adminColors)];
        }

        // Daily 14 Days Range
        $daysRange = [];
        $dailyLabels = [];
        for ($i = 13; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $daysRange[] = $dateStr;
            $dailyLabels[] = now()->subDays($i)->format('d M');
        }

        $rawDailyAdmin = (clone $query)
            ->selectRaw("{$dateExpr} as date, assigned_to, COUNT(*) as total")
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->whereNotNull('assigned_to')
            ->groupBy('date', 'assigned_to')
            ->get();

        $rawDailyTotal = (clone $query)
            ->selectRaw("{$dateExpr} as date, COUNT(*) as total")
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date');

        $adminDailyMap = [];
        foreach ($rawDailyAdmin as $row) {
            $adminDailyMap[$row->assigned_to][$row->date] = (int)$row->total;
        }

        $dailyTotalValues = [];
        $dailyTotal14d = 0;
        foreach ($daysRange as $d) {
            $tVal = (int)($rawDailyTotal[$d] ?? 0);
            $dailyTotalValues[] = $tVal;
            $dailyTotal14d += $tVal;
        }

        $dailyDatasets = [
            [
                'admin_id' => 'total',
                'label' => 'Total Semua Tiket',
                'data' => $dailyTotalValues,
                'borderColor' => '#1e293b',
                'backgroundColor' => 'rgba(30, 41, 59, 0.08)',
                'pointBackgroundColor' => '#1e293b',
                'pointBorderColor' => '#ffffff',
                'borderWidth' => 3,
                'pointRadius' => 4.5,
                'pointHoverRadius' => 7,
                'tension' => 0.35,
                'fill' => false,
            ]
        ];
        $adminDailySummary = [];

        foreach ($admins as $index => $admin) {
            $color = $adminColorLookup[$admin->id] ?? $adminColors[$index % count($adminColors)];
            $values = [];
            $total14d = 0;
            foreach ($daysRange as $d) {
                $cnt = $adminDailyMap[$admin->id][$d] ?? 0;
                $values[] = $cnt;
                $total14d += $cnt;
            }

            $dailyDatasets[] = [
                'admin_id' => $admin->id,
                'label' => $admin->name ?? ($admin->username ?? 'IT #' . $admin->id),
                'data' => $values,
                'borderColor' => $color,
                'backgroundColor' => $color . '20',
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#ffffff',
                'borderWidth' => 2.5,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'tension' => 0.35,
                'fill' => false,
            ];

            $adminDailySummary[] = [
                'id' => $admin->id,
                'name' => $admin->name ?? ($admin->username ?? 'IT #' . $admin->id),
                'color' => $color,
                'total_14d' => $total14d,
            ];
        }

        $dailyValues = $dailyTotalValues;

        // MONTHLY
        $monthly = (clone $query)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as total")
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $bulanIndonesia = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agust',
            9 => 'Sept', 10 => 'Okt', 11 => 'Nove', 12 => 'Des',
        ];

        $monthlyLabels = $monthly->map(function ($item) use ($bulanIndonesia) {
            return $bulanIndonesia[$item->month] ?? (string)$item->month;
        })->values();

        $monthlyValues = $monthly->pluck('total')->values();

        if ($monthlyLabels->isEmpty()) {
            $monthlyLabels = collect([$bulanIndonesia[(int)now()->format('n')]]);
            $monthlyValues = collect([0]);
        }

        // KATEGORI (Filtered by Month)
        $kategoriQuery = (clone $query);
        if ($kategoriMonth !== 'all' && (int)$kategoriMonth > 0) {
            $kategoriQuery->whereYear('created_at', now()->year)->whereMonth('created_at', (int)$kategoriMonth);
        }

        $kategoriData = $kategoriQuery
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $kategoriLabels = ['Hardware', 'Software', 'Network', 'Other'];
        $kategoriValues = [
            (int)($kategoriData['hardware'] ?? 0),
            (int)($kategoriData['software'] ?? 0),
            (int)($kategoriData['network'] ?? 0),
            (int)($kategoriData['other'] ?? 0),
        ];

        // SLA (dalam Menit)
        $diffMinutesExpr = $isSqlite ? '((strftime(\'%s\', resolved_at) - strftime(\'%s\', started_at)) / 60)' : 'TIMESTAMPDIFF(MINUTE, started_at, resolved_at)';
        $sla_avg = Ticket::whereNotNull('started_at')
            ->whereNotNull('resolved_at')
            ->avg(DB::raw($diffMinutesExpr));
        $slaMinutes = round($sla_avg ?? 0);

        // LATEST OPEN TICKETS FOR QUICK ACTION
        $latestOpenTickets = Ticket::where('status', 'open')->latest()->take(5)->get();

        // WORKLOAD (Filtered by Month, Default Current Month)
        $workloadQuery = Ticket::select(
            'assigned_to',
            DB::raw('COUNT(*) as total_ticket')
        )
        ->whereNotNull('assigned_to')
        ->whereYear('created_at', now()->year);

        if ($workloadMonth !== 'all' && (int)$workloadMonth > 0) {
            $workloadQuery->whereMonth('created_at', (int)$workloadMonth);
        }

        $technicianWorkload = $workloadQuery
            ->groupBy('assigned_to')
            ->with('technician')
            ->orderByDesc('total_ticket')
            ->get();

        $workloadLabels = [];
        $workloadValues = [];
        $workloadColors = [];
        foreach ($technicianWorkload as $tech) {
            $adminId = $tech->assigned_to;
            $adminName = $tech->technician?->name ?? ($tech->technician?->username ?? 'IT');
            $color = $adminColorLookup[$adminId] ?? '#0284c7';

            $workloadLabels[] = $adminName;
            $workloadValues[] = (int)$tech->total_ticket;
            $workloadColors[] = $color;
        }

        // STATISTIK TIKET PER LAPTOP & PENGGUNA (HANYA KODE LAPTOP "LAP-xxx", ABAIKAN HED, LAN, CHA)
        $inventories = \App\Models\Inventory::where(function ($q) {
            $q->where('sn', 'LIKE', 'LAP%')
              ->orWhere('jenis', 'LIKE', '%laptop%');
        })->get();

        $ticketStatsByLaptop = Ticket::select(
                'nomor_laptop',
                DB::raw('COUNT(*) as total_ticket'),
                DB::raw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count"),
                DB::raw("SUM(CASE WHEN status = 'on_progress' THEN 1 ELSE 0 END) as progress_count"),
                DB::raw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count"),
                DB::raw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count")
            )
            ->whereNotNull('nomor_laptop')
            ->where(function ($q) {
                $q->where('nomor_laptop', 'LIKE', 'LAP%')
                  ->orWhere('nomor_laptop', 'LIKE', '%LAP%');
            })
            ->groupBy('nomor_laptop')
            ->get()
            ->keyBy('nomor_laptop');

        $laptopStatsList = collect();

        foreach ($inventories as $inv) {
            $sn = $inv->sn;
            $stats = $ticketStatsByLaptop->get($sn);
            if (!$stats && $sn) {
                foreach ($ticketStatsByLaptop as $laptopKey => $st) {
                    if ($laptopKey && (str_contains($laptopKey, $sn) || str_contains($sn, $laptopKey))) {
                        $stats = $st;
                        break;
                    }
                }
            }

            $laptopStatsList->push([
                'nomor_laptop' => $sn ?: ($inv->keterangan ?: 'N/A'),
                'pengguna'     => $inv->pengguna ?? '-',
                'department'   => $inv->department ?? '-',
                'total_ticket' => $stats ? (int)$stats->total_ticket : 0,
                'open'         => $stats ? (int)$stats->open_count : 0,
                'progress'     => $stats ? (int)$stats->progress_count : 0,
                'pending'      => $stats ? (int)$stats->pending_count : 0,
                'closed'       => $stats ? (int)$stats->closed_count : 0,
            ]);
        }

        foreach ($ticketStatsByLaptop as $laptopKey => $stats) {
            if (!$laptopKey || !str_contains(strtoupper($laptopKey), 'LAP')) continue;
            $alreadyIncluded = $inventories->contains(function ($inv) use ($laptopKey) {
                return $inv->sn === $laptopKey || ($inv->sn && (str_contains($inv->sn, $laptopKey) || str_contains($laptopKey, $inv->sn)));
            });

            if (!$alreadyIncluded) {
                $latestTicket = Ticket::where('nomor_laptop', $laptopKey)->latest()->first();
                $laptopStatsList->push([
                    'nomor_laptop' => $laptopKey,
                    'pengguna'     => $latestTicket?->nama ?? '-',
                    'department'   => '-',
                    'total_ticket' => (int)$stats->total_ticket,
                    'open'         => (int)$stats->open_count,
                    'progress'     => (int)$stats->progress_count,
                    'pending'      => (int)$stats->pending_count,
                    'closed'       => (int)$stats->closed_count,
                ]);
            }
        }

        $laptopStats = $laptopStatsList->sortByDesc('total_ticket')->values();

        return view('admin.dashboard', compact(
            'total',
            'open',
            'progress',
            'pending',
            'closed',
            'cancelled',
            'today',
            'dailyLabels',
            'dailyValues',
            'dailyDatasets',
            'dailyTotal14d',
            'adminDailySummary',
            'monthsList',
            'currentMonth',
            'kategoriMonth',
            'workloadMonth',
            'monthly',
            'monthlyLabels',
            'monthlyValues',
            'kategoriLabels',
            'kategoriValues',
            'sla_avg',
            'slaMinutes',
            'latestOpenTickets',
            'technicianWorkload',
            'workloadLabels',
            'workloadValues',
            'workloadColors',
            'laptopStats'
        ));
    }


    public function realtime(Request $request)
    {
        try {
            $user = auth()->user();

            $query = Ticket::query();
            if ($user && $user->role == 'user') {
                $query->where('user_id', $user->id);
            }

            // Stats
            $total = (clone $query)->count();
            $open = (clone $query)->where('status','open')->count();
            $progress = (clone $query)->where('status','on_progress')->count();
            $pending = (clone $query)->where('status','pending')->count();
            $closed = (clone $query)->where('status','closed')->count();
            $cancelled = (clone $query)->where('status','cancelled')->count();

            // Daily 14-day series multi-line per admin + total line
            $isSqlite = DB::connection()->getDriverName() === 'sqlite';
            $dateExpr = $isSqlite ? 'date(created_at)' : 'DATE(created_at)';
            $monthExpr = $isSqlite ? 'cast(strftime(\'%m\', created_at) as integer)' : 'MONTH(created_at)';

            $adminColors = [
                '#0284c7', // Sky Blue
                '#10b981', // Emerald
                '#f59e0b', // Amber
                '#8b5cf6', // Violet
                '#ec4899', // Pink
                '#14b8a6', // Teal
                '#6366f1', // Indigo
                '#f97316', // Orange
            ];

            $currentMonth = (int)now()->format('n');
            $kategoriMonth = $request->input('kategori_month', $currentMonth);
            $workloadMonth = $request->input('workload_month', $currentMonth);

            $admins = User::whereIn('role', ['admin', 'technician'])->orderBy('id')->get();
            if ($admins->isEmpty()) {
                $assignedUserIds = Ticket::whereNotNull('assigned_to')->distinct()->pluck('assigned_to');
                $admins = User::whereIn('id', $assignedUserIds)->orderBy('id')->get();
            }

            $adminColorLookup = [];
            foreach ($admins as $index => $adm) {
                $adminColorLookup[$adm->id] = $adminColors[$index % count($adminColors)];
            }

            $daysRange = [];
            $dailyLabels = [];
            for ($i = 13; $i >= 0; $i--) {
                $dateStr = now()->subDays($i)->format('Y-m-d');
                $daysRange[] = $dateStr;
                $dailyLabels[] = now()->subDays($i)->format('d M');
            }

            $rawDailyAdmin = (clone $query)
                ->selectRaw("{$dateExpr} as date, assigned_to, COUNT(*) as total")
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->whereNotNull('assigned_to')
                ->groupBy('date', 'assigned_to')
                ->get();

            $rawDailyTotal = (clone $query)
                ->selectRaw("{$dateExpr} as date, COUNT(*) as total")
                ->where('created_at', '>=', now()->subDays(13)->startOfDay())
                ->groupBy('date')
                ->pluck('total', 'date');

            $adminDailyMap = [];
            foreach ($rawDailyAdmin as $row) {
                $adminDailyMap[$row->assigned_to][$row->date] = (int)$row->total;
            }

            $dailyTotalValues = [];
            $dailyTotal14d = 0;
            foreach ($daysRange as $d) {
                $tVal = (int)($rawDailyTotal[$d] ?? 0);
                $dailyTotalValues[] = $tVal;
                $dailyTotal14d += $tVal;
            }

            $dailyDatasets = [
                [
                    'admin_id' => 'total',
                    'label' => 'Total Semua Tiket',
                    'data' => $dailyTotalValues,
                    'borderColor' => '#1e293b',
                    'backgroundColor' => 'rgba(30, 41, 59, 0.08)',
                    'pointBackgroundColor' => '#1e293b',
                    'pointBorderColor' => '#ffffff',
                    'borderWidth' => 3,
                    'pointRadius' => 4.5,
                    'pointHoverRadius' => 7,
                    'tension' => 0.35,
                    'fill' => false,
                ]
            ];
            $adminDailySummary = [];

            foreach ($admins as $index => $admin) {
                $color = $adminColorLookup[$admin->id] ?? $adminColors[$index % count($adminColors)];
                $values = [];
                $total14d = 0;
                foreach ($daysRange as $d) {
                    $cnt = $adminDailyMap[$admin->id][$d] ?? 0;
                    $values[] = $cnt;
                    $total14d += $cnt;
                }

                $dailyDatasets[] = [
                    'admin_id' => $admin->id,
                    'label' => $admin->name ?? ($admin->username ?? 'IT #' . $admin->id),
                    'data' => $values,
                    'borderColor' => $color,
                    'backgroundColor' => $color . '20',
                    'pointBackgroundColor' => $color,
                    'pointBorderColor' => '#ffffff',
                    'borderWidth' => 2.5,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'tension' => 0.35,
                    'fill' => false,
                ];

                $adminDailySummary[] = [
                    'id' => $admin->id,
                    'name' => $admin->name ?? ($admin->username ?? 'IT #' . $admin->id),
                    'color' => $color,
                    'total_14d' => $total14d,
                ];
            }

            $dailyValues = $dailyTotalValues;

            // Monthly
            $monthly = (clone $query)
                ->selectRaw("{$monthExpr} as bulan, COUNT(*) as total")
                ->whereYear('created_at', now()->year)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            $bulanIndonesia = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agust',
                9 => 'Sept', 10 => 'Okt', 11 => 'Nove', 12 => 'Des',
            ];

            $monthlyLabels = $monthly->map(function($item) use ($bulanIndonesia){
                return $bulanIndonesia[$item->bulan] ?? (string)$item->bulan;
            })->values();

            $monthlyValues = $monthly->pluck('total')->values();
            if ($monthlyLabels->isEmpty()) {
                $monthlyLabels = collect([$bulanIndonesia[(int)now()->format('n')]]);
                $monthlyValues = collect([0]);
            }

            // Kategori (Filtered by Month)
            $kategoriQuery = (clone $query);
            if ($kategoriMonth !== 'all' && (int)$kategoriMonth > 0) {
                $kategoriQuery->whereYear('created_at', now()->year)->whereMonth('created_at', (int)$kategoriMonth);
            }

            $kategoriData = $kategoriQuery
                ->selectRaw('kategori, COUNT(*) as total')
                ->groupBy('kategori')
                ->pluck('total', 'kategori');

            $kategoriLabels = ['Hardware', 'Software', 'Network', 'Other'];
            $kategoriValues = [
                (int)($kategoriData['hardware'] ?? 0),
                (int)($kategoriData['software'] ?? 0),
                (int)($kategoriData['network'] ?? 0),
                (int)($kategoriData['other'] ?? 0),
            ];

            // Workload (Filtered by Month)
            $workloadQuery = Ticket::select(
                'assigned_to',
                DB::raw('COUNT(*) as total_ticket')
            )
            ->whereNotNull('assigned_to')
            ->whereYear('created_at', now()->year);

            if ($workloadMonth !== 'all' && (int)$workloadMonth > 0) {
                $workloadQuery->whereMonth('created_at', (int)$workloadMonth);
            }

            $technicianWorkload = $workloadQuery
                ->groupBy('assigned_to')
                ->with('technician')
                ->orderByDesc('total_ticket')
                ->get();

            $workloadLabels = [];
            $workloadValues = [];
            $workloadColors = [];
            $workloadTable = [];

            foreach ($technicianWorkload as $tech) {
                $adminId = $tech->assigned_to;
                $adminName = $tech->technician?->name ?? ($tech->technician?->username ?? 'IT');
                $color = $adminColorLookup[$adminId] ?? '#0284c7';

                $workloadLabels[] = $adminName;
                $workloadValues[] = (int)$tech->total_ticket;
                $workloadColors[] = $color;
                $workloadTable[] = [
                    'name' => $adminName,
                    'total' => (int)$tech->total_ticket,
                    'color' => $color,
                ];
            }

            // Latest Open Tickets
            $latestOpenTickets = Ticket::where('status', 'open')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($t) {
                    return [
                        'id'               => $t->id,
                        'ticket_code'      => $t->ticket_code,
                        'nama'             => $t->nama,
                        'nomor_laptop'     => $t->nomor_laptop ?? '-',
                        'kategori'         => strtoupper($t->kategori ?? 'OTHER'),
                        'deskripsi'        => \Illuminate\Support\Str::limit($t->deskripsi, 60),
                        'created_at_human' => $t->created_at ? $t->created_at->diffForHumans() : '-',
                        'show_url'         => url('admin/ticket/show/' . $t->id),
                    ];
                });

            // Latest Ticket for notification alert
            $latestTicket = Ticket::latest()->first();
            $latestTicketId = $latestTicket?->id ?? 0;
            $latestTicketMessage = $latestTicket
                ? "Tiket #{$latestTicket->ticket_code} dari {$latestTicket->nama} ({$latestTicket->nomor_laptop})"
                : "Tidak ada tiket";

            return response()->json([
                'total'                 => $total,
                'open'                  => $open,
                'progress'              => $progress,
                'pending'               => $pending,
                'closed'                => $closed,
                'cancelled'             => $cancelled,
                'daily_labels'          => $dailyLabels,
                'daily_values'          => $dailyValues,
                'daily_datasets'        => $dailyDatasets,
                'daily_total_14d'       => $dailyTotal14d,
                'admin_daily_summary'   => $adminDailySummary,
                'monthly_labels'        => $monthlyLabels,
                'monthly_values'        => $monthlyValues,
                'kategori_labels'       => $kategoriLabels,
                'kategori_values'       => $kategoriValues,
                'workload_labels'       => $workloadLabels,
                'workload_values'       => $workloadValues,
                'workload_colors'       => $workloadColors,
                'workload_table'        => $workloadTable,
                'latest_open_tickets'   => $latestOpenTickets,
                'latest_ticket_id'      => $latestTicketId,
                'latest_ticket_message' => $latestTicketMessage,
                'selected_kategori_month' => $kategoriMonth,
                'selected_workload_month' => $workloadMonth,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}