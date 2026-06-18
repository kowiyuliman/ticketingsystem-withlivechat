<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetLoan;
use App\Models\AssetRepair;
use App\Models\AssetDamage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetsImport;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized action. Inventory is restricted to administrators.');
            }
            return $next($request);
        });
    }

    // 1. Dashboard Inventory & Statistik
    public function index()
    {
        $stats = [
            'total'     => Asset::count(),
            'available' => Asset::where('status', 'available')->count(),
            'assigned'  => Asset::where('status', 'assigned')->count(),
            'on_loan'   => Asset::where('status', 'on_loan')->count(),
            'in_repair' => Asset::where('status', 'in_repair')->count(),
            'damaged'   => Asset::where('status', 'damaged')->count(),
        ];

        // Dapatkan data grafik per tipe asset
        $typeStats = Asset::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type');

        $types = Asset::typeLabels();

        return view('admin.inventory.index', compact('stats', 'typeStats', 'types'));
    }

    // 2. Daftar & CRUD Asset
    public function assets(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->latest()->paginate(15);
        $types = Asset::typeLabels();
        $statuses = Asset::statusLabels();

        return view('admin.inventory.assets.index', compact('assets', 'types', 'statuses'));
    }

    public function createAsset()
    {
        $types = Asset::typeLabels();
        $brands = Asset::laptopBrands();
        return view('admin.inventory.assets.create', compact('types', 'brands'));
    }

    public function storeAsset(Request $request)
    {
        $request->validate([
            'type'          => 'required|in:laptop,charger,mouse,lan_adapter,headset,usb_audio',
            'brand'         => 'required_if:type,laptop|nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number',
            'condition'     => 'required|in:good,repair,damaged',
            'notes'         => 'nullable|string',
        ]);

        Asset::create([
            'type'          => $request->type,
            'brand'         => $request->brand,
            'model'         => $request->model,
            'serial_number' => $request->serial_number,
            'condition'     => $request->condition,
            'status'        => $request->condition === 'good' ? 'available' : ($request->condition === 'repair' ? 'in_repair' : 'damaged'),
            'notes'         => $request->notes,
        ]);

        return redirect()->route('admin.inventory.assets')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function editAsset($id)
    {
        $asset = Asset::findOrFail($id);
        $types = Asset::typeLabels();
        $brands = Asset::laptopBrands();
        return view('admin.inventory.assets.edit', compact('asset', 'types', 'brands'));
    }

    public function updateAsset(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $request->validate([
            'type'          => 'required|in:laptop,charger,mouse,lan_adapter,headset,usb_audio',
            'brand'         => 'required_if:type,laptop|nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100|unique:assets,serial_number,' . $id,
            'condition'     => 'required|in:good,repair,damaged',
            'status'        => 'required|in:available,assigned,on_loan,in_repair,damaged',
            'notes'         => 'nullable|string',
        ]);

        $asset->update($request->all());

        return redirect()->route('admin.inventory.assets')->with('success', 'Asset berhasil diperbarui.');
    }

    public function deleteAsset($id)
    {
        $asset = Asset::findOrFail($id);
        
        if ($asset->assignments()->whereNull('returned_at')->exists()) {
            return redirect()->back()->with('error', 'Asset tidak bisa dihapus karena sedang di-assign ke user.');
        }

        $asset->delete();
        return redirect()->route('admin.inventory.assets')->with('success', 'Asset berhasil dihapus.');
    }

    // 3. Asset Assignments (1 user = 1 set asset 5-6 item)
    public function assignments()
    {
        // Kelompokkan assignment aktif berdasarkan user
        $users = User::whereHas('assignments', function($q) {
            $q->whereNull('returned_at');
        })->with(['assignments' => function($q) {
            $q->whereNull('returned_at')->with('asset');
        }])->paginate(10);

        return view('admin.inventory.assignments.index', compact('users'));
    }

    public function createAssignment()
    {
        $users = User::all();
        
        // Ambil list asset yang available per tipe
        $laptops     = Asset::available()->where('type', 'laptop')->get();
        $chargers    = Asset::available()->where('type', 'charger')->get();
        $mice        = Asset::available()->where('type', 'mouse')->get();
        $lanAdapters = Asset::available()->where('type', 'lan_adapter')->get();
        $headsets    = Asset::available()->where('type', 'headset')->get();
        $usbAudios   = Asset::available()->where('type', 'usb_audio')->get();

        return view('admin.inventory.assignments.create', compact('users', 'laptops', 'chargers', 'mice', 'lanAdapters', 'headsets', 'usbAudios'));
    }

    public function storeAssignment(Request $request)
    {
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'laptop_id'      => 'nullable|exists:assets,id',
            'charger_id'     => 'nullable|exists:assets,id',
            'mouse_id'       => 'nullable|exists:assets,id',
            'lan_adapter_id' => 'nullable|exists:assets,id',
            'headset_id'     => 'nullable|exists:assets,id',
            'usb_audio_id'   => 'nullable|exists:assets,id',
        ]);

        $assetIds = array_filter([
            $request->laptop_id,
            $request->charger_id,
            $request->mouse_id,
            $request->lan_adapter_id,
            $request->headset_id,
            $request->usb_audio_id,
        ]);

        if (empty($assetIds)) {
            return redirect()->back()->withErrors(['error' => 'Pilih minimal satu asset untuk di-assign.'])->withInput();
        }

        DB::transaction(function() use ($request, $assetIds) {
            foreach ($assetIds as $assetId) {
                // Buat assignment aktif
                AssetAssignment::create([
                    'user_id'     => $request->user_id,
                    'asset_id'    => $assetId,
                    'assigned_at' => now(),
                    'assigned_by' => Auth::id(),
                ]);

                // Update status asset menjadi assigned
                Asset::where('id', $assetId)->update(['status' => 'assigned']);
            }
        });

        return redirect()->route('admin.inventory.assignments')->with('success', 'Asset berhasil di-assign ke user.');
    }

    public function returnAssignment($id)
    {
        $assignment = AssetAssignment::findOrFail($id);

        DB::transaction(function() use ($assignment) {
            $assignment->update([
                'returned_at' => now(),
            ]);

            // Kembalikan status asset menjadi available
            $assignment->asset->update([
                'status' => 'available'
            ]);
        });

        return redirect()->back()->with('success', 'Asset berhasil dikembalikan (unassigned).');
    }

    // 4. Peminjaman Laptop (bawa pulang + jaminan KTP/SIM)
    public function loans()
    {
        $loans = AssetLoan::with(['user', 'assignment.asset'])->latest()->paginate(15);
        return view('admin.inventory.loans.index', compact('loans'));
    }

    public function createLoan()
    {
        // Ambil user yang memiliki active laptop assignment yang belum dipinjamkan
        $activeLaptopAssignments = AssetAssignment::whereNull('returned_at')
            ->whereHas('asset', function($q) {
                $q->where('type', 'laptop')->where('status', 'assigned');
            })
            ->with(['user', 'asset'])
            ->get();

        return view('admin.inventory.loans.create', compact('activeLaptopAssignments'));
    }

    public function storeLoan(Request $request)
    {
        $request->validate([
            'asset_assignment_id' => 'required|exists:asset_assignments,id',
            'guarantee_type'      => 'required|in:ktp,sim',
            'guarantee_number'    => 'required|string|max:50',
            'loan_date'           => 'required|date',
            'notes'               => 'nullable|string',
        ]);

        $assignment = AssetAssignment::findOrFail($request->asset_assignment_id);

        // Pastikan asset masih berstatus assigned
        if ($assignment->asset->status !== 'assigned') {
            return redirect()->back()->withErrors(['error' => 'Laptop tidak tersedia untuk dipinjam (kemungkinan sedang dipinjam atau direpair).']);
        }

        DB::transaction(function() use ($request, $assignment) {
            AssetLoan::create([
                'asset_assignment_id' => $assignment->id,
                'user_id'             => $assignment->user_id,
                'loan_date'           => $request->loan_date,
                'guarantee_type'      => $request->guarantee_type,
                'guarantee_number'    => $request->guarantee_number,
                'status'              => 'borrowed',
                'notes'               => $request->notes,
                'approved_by'         => Auth::id(),
            ]);

            // Update status asset menjadi on_loan
            $assignment->asset->update(['status' => 'on_loan']);
        });

        return redirect()->route('admin.inventory.loans')->with('success', 'Peminjaman laptop berhasil disimpan.');
    }

    public function returnLoan($id)
    {
        $loan = AssetLoan::findOrFail($id);

        if ($loan->status === 'returned') {
            return redirect()->back()->with('error', 'Peminjaman ini sudah dikembalikan.');
        }

        DB::transaction(function() use ($loan) {
            $loan->update([
                'return_date' => now()->toDateString(),
                'status'      => 'returned',
            ]);

            // Kembalikan status asset dari on_loan menjadi assigned kembali ke user
            $loan->assignment->asset->update(['status' => 'assigned']);
        });

        return redirect()->route('admin.inventory.loans')->with('success', 'Laptop berhasil dikembalikan dari peminjaman.');
    }

    // 5. Asset Repair / Maintenance
    public function repairs()
    {
        $repairs = AssetRepair::with(['asset', 'reporter'])->latest()->paginate(15);
        return view('admin.inventory.repairs.index', compact('repairs'));
    }

    public function createRepair()
    {
        // Tampilkan semua asset yang bukan sedang di repair atau rusak parah
        $assets = Asset::whereNotIn('status', ['in_repair', 'damaged'])->get();
        return view('admin.inventory.repairs.create', compact('assets'));
    }

    public function storeRepair(Request $request)
    {
        $request->validate([
            'asset_id'          => 'required|exists:assets,id',
            'issue_description' => 'required|string',
            'repair_date'       => 'required|date',
            'repair_vendor'     => 'nullable|string|max:100',
            'repair_cost'       => 'nullable|numeric|min:0',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        DB::transaction(function() use ($request, $asset) {
            // Buat record repair
            AssetRepair::create([
                'asset_id'          => $request->asset_id,
                'reported_by'       => Auth::id(),
                'issue_description' => $request->issue_description,
                'repair_date'       => $request->repair_date,
                'repair_vendor'     => $request->repair_vendor,
                'repair_cost'       => $request->repair_cost,
                'status'            => 'in_progress',
            ]);

            // Update status asset menjadi in_repair dan condition repair
            $asset->update([
                'status'    => 'in_repair',
                'condition' => 'repair'
            ]);

            // Jika asset sedang di-assign ke user, unassign dulu agar available setelah repair selesai
            $activeAssignment = $asset->activeAssignment;
            if ($activeAssignment) {
                $activeAssignment->update(['returned_at' => now()]);
            }
        });

        return redirect()->route('admin.inventory.repairs')->with('success', 'Laporan repair berhasil disimpan, status asset diubah menjadi In Repair.');
    }

    public function updateRepairStatus(Request $request, $id)
    {
        $repair = AssetRepair::findOrFail($id);

        $request->validate([
            'status'         => 'required|in:completed,cancelled',
            'completed_date' => 'required_if:status,completed|nullable|date',
            'resolution'     => 'required_if:status,completed|nullable|string',
            'repair_cost'    => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($request, $repair) {
            $repair->update([
                'status'         => $request->status,
                'completed_date' => $request->status === 'completed' ? $request->completed_date : null,
                'resolution'     => $request->status === 'completed' ? $request->resolution : null,
                'repair_cost'    => $request->repair_cost ?? $repair->repair_cost,
            ]);

            if ($request->status === 'completed') {
                // Selesai repair: status kembali available & kondisi good
                $repair->asset->update([
                    'status'    => 'available',
                    'condition' => 'good',
                ]);
            } else {
                // Cancelled: status kembali ke available / tergantung kondisi awal
                $repair->asset->update([
                    'status'    => 'available',
                    'condition' => 'good',
                ]);
            }
        });

        return redirect()->route('admin.inventory.repairs')->with('success', 'Status repair berhasil diperbarui.');
    }

    // 6. Management Asset Rusak (Damaged Assets)
    public function damages()
    {
        $damages = AssetDamage::with(['asset', 'reporter', 'replacementAsset'])->latest()->paginate(15);
        $assetsAvailable = Asset::available()->get();
        return view('admin.inventory.damages.index', compact('damages', 'assetsAvailable'));
    }

    public function createDamage()
    {
        // Ambil semua asset yang statusnya bukan damaged
        $assets = Asset::where('status', '!=', 'damaged')->get();
        return view('admin.inventory.damages.create', compact('assets'));
    }

    public function storeDamage(Request $request)
    {
        $request->validate([
            'asset_id'           => 'required|exists:assets,id',
            'damage_date'        => 'required|date',
            'damage_description' => 'required|string',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        DB::transaction(function() use ($request, $asset) {
            // Catat kerusakan
            AssetDamage::create([
                'asset_id'           => $request->asset_id,
                'reported_by'        => Auth::id(),
                'damage_date'        => $request->damage_date,
                'damage_description' => $request->damage_description,
                'action_taken'       => 'pending',
            ]);

            // Update status asset menjadi damaged
            $asset->update([
                'status'    => 'damaged',
                'condition' => 'damaged',
            ]);

            // Jika asset sedang di-assign ke user, matikan assignment-nya
            $activeAssignment = $asset->activeAssignment;
            if ($activeAssignment) {
                $activeAssignment->update(['returned_at' => now()]);
            }
        });

        return redirect()->route('admin.inventory.damages')->with('success', 'Laporan asset rusak berhasil disimpan.');
    }

    public function updateDamage(Request $request, $id)
    {
        $damage = AssetDamage::findOrFail($id);

        $request->validate([
            'action_taken'         => 'required|in:repair,dispose,replace',
            'replacement_asset_id' => 'required_if:action_taken,replace|nullable|exists:assets,id',
            'notes'                => 'nullable|string',
        ]);

        DB::transaction(function() use ($request, $damage) {
            $damage->update([
                'action_taken'         => $request->action_taken,
                'replacement_asset_id' => $request->action_taken === 'replace' ? $request->replacement_asset_id : null,
                'notes'                => $request->notes,
            ]);

            $asset = $damage->asset;

            if ($request->action_taken === 'repair') {
                // Buat laporan repair baru secara otomatis
                AssetRepair::create([
                    'asset_id'          => $asset->id,
                    'reported_by'       => Auth::id(),
                    'issue_description' => 'Auto-created from damage report: ' . $damage->damage_description,
                    'repair_date'       => now()->toDateString(),
                    'status'            => 'in_progress',
                ]);

                $asset->update([
                    'status'    => 'in_repair',
                    'condition' => 'repair',
                ]);
            } elseif ($request->action_taken === 'replace' && $request->replacement_asset_id) {
                $replacement = Asset::findOrFail($request->replacement_asset_id);
                
                // Cari user terakhir yang memakai asset ini
                $lastAssignment = AssetAssignment::where('asset_id', $asset->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastAssignment) {
                    // Assign replacement asset ke user tersebut secara otomatis
                    AssetAssignment::create([
                        'user_id'     => $lastAssignment->user_id,
                        'asset_id'    => $replacement->id,
                        'assigned_at' => now(),
                        'assigned_by' => Auth::id(),
                    ]);

                    $replacement->update(['status' => 'assigned']);
                }
            }
        });

        return redirect()->route('admin.inventory.damages')->with('success', 'Tindakan penanganan asset rusak berhasil disimpan.');
    }

    // Bulk Import Assets
    public function importAsset(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new AssetsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Import asset dari spreadsheet berhasil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengimpor asset: ' . $e->getMessage());
        }
    }

    // Download Asset Template CSV
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_asset.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header kolom
            fputcsv($file, ['type', 'brand', 'model', 'serial_number', 'condition', 'notes']);
            
            // Baris contoh untuk memandu admin
            fputcsv($file, ['laptop', 'HP', 'EliteBook 840 G8', '5CD12345XYZ', 'good', 'Laptop IT']);
            fputcsv($file, ['charger', 'HP', '65W USB-C', 'SN-CHARGER-01', 'good', '']);
            fputcsv($file, ['mouse', 'Logitech', 'G502 Hero', 'SN-MOUSE-01', 'good', '']);
            fputcsv($file, ['lan_adapter', 'TPLink', 'Gigabit Ethernet', 'SN-LAN-01', 'repair', 'Ada kendala disconnect']);
            fputcsv($file, ['headset', 'Jabra', 'Evolve 20', 'SN-HEADSET-01', 'good', '']);
            fputcsv($file, ['usb_audio', 'Vention', 'USB Sound Card', 'SN-AUDIO-01', 'damaged', 'Rusak mati total']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
