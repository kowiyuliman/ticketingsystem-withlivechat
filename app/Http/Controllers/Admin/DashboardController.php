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
            ];
        });

        // ASSIGN
        $total = $stats['total'];
        $open = $stats['open'];
        $progress = $stats['progress'];
        $pending = $stats['pending'];
        $closed = $stats['closed'];

        // TODAY
        $today = (clone $query)->whereDate('created_at', now())->count();

        // DAILY
        $daily = (clone $query)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->pluck('total','date');

        // MONTHLY
        $monthly = (clone $query)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total','month');

        // KATEGORI
        $kategori = (clone $query)
            ->selectRaw('kategori, COUNT(*) as total')
            ->groupBy('kategori')
            ->pluck('total','kategori');

        // SLA
        $sla_avg = Ticket::whereNotNull('started_at')
            ->whereNotNull('resolved_at')
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, started_at, resolved_at)'));

        // WORKLOAD
        $technicianWorkload = Ticket::select(
            'assigned_to',
            DB::raw('COUNT(*) as total_ticket')
        )
        ->whereNotNull('assigned_to')
        ->groupBy('assigned_to')
        ->with('technician')
        ->orderByDesc('total_ticket')
        ->get();

        return view('admin.dashboard', compact(
            'total','open','progress','pending','closed','today',
            'daily','monthly','kategori','sla_avg','technicianWorkload'
        ));
    }

    public function realtime()
    {
        try{

            $daily = Ticket::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total','date');

            $monthly = Ticket::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total','month');

            $kategori = Ticket::selectRaw('kategori, COUNT(*) as total')
                ->groupBy('kategori')
                ->pluck('total','kategori');

            // $technicianWorkload = Ticket::selectRaw('technician_id, COUNT(*) as total_ticket')
            //     ->with('technician')
            //     ->groupBy('technician_id')
            //     ->get();

            return response()->json([
                'total' => Ticket::count(),
                'open' => Ticket::where('status','open')->count(),
                'progress' => Ticket::where('status','on_progress')->count(),
                'pending' => Ticket::where('status','pending')->count(),
                'closed' => Ticket::where('status','closed')->count(),

                'daily' => $daily,
                'monthly' => $monthly,
                'kategori' => $kategori,

                // 'workload_labels' => $technicianWorkload->map(function($t){
                //     return $t->technician->name ?? '-';
                // }),

                // 'workload_values' =>
                //     $technicianWorkload->pluck('total_ticket'),
            ]);

        }catch(\Exception $e){

            return response()->json([
                'error' => $e->getMessage()
            ],500);

        }

        return response()->json([
            'total' => 10
        ]);
    }
}