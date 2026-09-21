<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;


// Public User Portal & Frictionless Ticketing Routes
Route::get('/', [TicketController::class, 'portal'])->name('portal');
Route::get('/portal', [TicketController::class, 'portal']);
Route::post('/set-laptop', [TicketController::class, 'setLaptop'])->name('laptop.set');
Route::post('/create-ticket', [TicketController::class, 'store'])->name('ticket.store');
Route::get('/ticket/{id}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/comments/{id}', [TicketController::class, 'fetchComments'])->name('ticket.comments');
Route::post('/ticket/comment/{id}', [TicketController::class, 'comment'])->name('ticket.comment');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Route user authenticated
Route::middleware(['auth'])->group(function(){
    Route::get('/my-tickets', [TicketController::class, 'index']);
    Route::get('/change-password', [ChangePasswordController::class, 'index']);
    Route::post('/change-password', [ChangePasswordController::class, 'update']);
});

Route::prefix('admin')->middleware(['auth'])->group(function(){

    Route::get('/dashboard',[DashboardController::class,'index'])->name('admin.dashboard');

    //route tiket
    Route::get('/tickets', [AdminTicketController::class,'index']);
    Route::get('/TicketAdmin', [AdminTicketController::class,'TicketAdmin']);
    Route::get('/ticket/show/{id}', [AdminTicketController::class,'show']);
    Route::get('/ticket/{id}/vnc', [AdminTicketController::class, 'downloadVncConfig']);
    Route::match(['GET', 'POST'], '/ticket/{id}/launch-vnc', [AdminTicketController::class, 'launchVnc']);
    Route::get('/ticket/edit/{id}', [AdminTicketController::class,'edit']);
    Route::post('/ticket/update/{id}', [AdminTicketController::class,'update']);
    Route::post('/ticket/comment/{id}', [AdminTicketController::class,'comment']);
    Route::delete('/ticket/delete/{id}', [AdminTicketController::class,'destroy']);

    // route cancel ticket
    Route::get('/cancelled', [AdminTicketController::class, 'cancelled']);
    Route::post('/ticket/cancel/{id}', [AdminTicketController::class, 'cancel']);

    //route get tiket
    Route::get('/ticket/take/{id}', [AdminTicketController::class,'takeTicket']);
    Route::post('/ticket/reassign/{id}', [AdminTicketController::class,'reassign']);
    Route::get('/tickets', [AdminTicketController::class,'index']);
    Route::get('/my-ticket', [AdminTicketController::class,'myTicket']);
    Route::post('/ticket/take/{id}', [AdminTicketController::class,'takeTicket']);

    //route report
    Route::get('/report', [AdminReportController::class,'index']);
    Route::get('/report/export', [AdminReportController::class,'export']);

    //route management users
    Route::get('/users', [AdminUserController::class,'index']);
    Route::get('/users/create', [AdminUserController::class,'create']);
    Route::post('/users/store', [AdminUserController::class,'store']);
    Route::get('/users/edit/{id}', [AdminUserController::class,'edit']);
    Route::post('/users/update/{id}', [AdminUserController::class,'update']);
    Route::delete('/users/delete/{id}', [AdminUserController::class,'destroy']);

    //route management khusus admin
    Route::middleware(['admin'])->group(function () {
        Route::get('/admins', [\App\Http\Controllers\Admin\AdminManagementController::class, 'index'])->name('admin.admins.index');
        Route::get('/admins/create', [\App\Http\Controllers\Admin\AdminManagementController::class, 'create'])->name('admin.admins.create');
        Route::post('/admins', [\App\Http\Controllers\Admin\AdminManagementController::class, 'store'])->name('admin.admins.store');
        Route::get('/admins/edit/{id}', [\App\Http\Controllers\Admin\AdminManagementController::class, 'edit'])->name('admin.admins.edit');
        Route::post('/admins/update/{id}', [\App\Http\Controllers\Admin\AdminManagementController::class, 'update'])->name('admin.admins.update');
        Route::delete('/admins/delete/{id}', [\App\Http\Controllers\Admin\AdminManagementController::class, 'destroy'])->name('admin.admins.delete');
    });

    //route import bulk user
    Route::post('/users/import', [AdminUserController::class,'import']);

    //route delete bulk
    Route::delete('/users/bulkDelete', [AdminUserController::class,'bulkDelete']);

    //auto refresh new ticket
    Route::get('/ticket/fetch', [AdminTicketController::class, 'fetchTickets']);

    //dashboard realtime
    Route::get('/dashboard/realtime', [DashboardController::class, 'realtime'])->name('admin.dashboard.realtime');
    Route::get('/notifications/unread-count', [AdminTicketController::class, 'unreadCount'])->name('admin.notifications.unreadCount');

    // merge ticket
    Route::post('/ticket/mergeTicket/{id}', [AdminTicketController::class, 'mergeTicket']);

    // search ticket
    Route::get('/ticket/searchTicket', [AdminTicketController::class, 'searchTicket']);

    // === INVENTORY ROUTES ===
    Route::prefix('inventory')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('admin.inventory.index');
        
        // Assets CRUD
        Route::get('/assets', [\App\Http\Controllers\Admin\InventoryController::class, 'assets'])->name('admin.inventory.assets');
        Route::get('/assets/create', [\App\Http\Controllers\Admin\InventoryController::class, 'createAsset'])->name('admin.inventory.assets.create');
        Route::post('/assets/store', [\App\Http\Controllers\Admin\InventoryController::class, 'storeAsset'])->name('admin.inventory.assets.store');
        Route::get('/assets/edit/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'editAsset'])->name('admin.inventory.assets.edit');
        Route::post('/assets/update/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'updateAsset'])->name('admin.inventory.assets.update');
        Route::delete('/assets/delete/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'deleteAsset'])->name('admin.inventory.assets.delete');
        Route::post('/assets/import', [\App\Http\Controllers\Admin\InventoryController::class, 'importAsset'])->name('admin.inventory.assets.import');
        Route::get('/assets/download-template', [\App\Http\Controllers\Admin\InventoryController::class, 'downloadTemplate'])->name('admin.inventory.assets.download-template');

        // Assignments
        Route::get('/assignments', [\App\Http\Controllers\Admin\InventoryController::class, 'assignments'])->name('admin.inventory.assignments');
        Route::get('/assignments/create', [\App\Http\Controllers\Admin\InventoryController::class, 'createAssignment'])->name('admin.inventory.assignments.create');
        Route::post('/assignments/store', [\App\Http\Controllers\Admin\InventoryController::class, 'storeAssignment'])->name('admin.inventory.assignments.store');
        Route::post('/assignments/return/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'returnAssignment'])->name('admin.inventory.assignments.return');

        // Loans
        Route::get('/loans', [\App\Http\Controllers\Admin\InventoryController::class, 'loans'])->name('admin.inventory.loans');
        Route::get('/loans/create', [\App\Http\Controllers\Admin\InventoryController::class, 'createLoan'])->name('admin.inventory.loans.create');
        Route::post('/loans/store', [\App\Http\Controllers\Admin\InventoryController::class, 'storeLoan'])->name('admin.inventory.loans.store');
        Route::post('/loans/return/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'returnLoan'])->name('admin.inventory.loans.return');

        // Repairs
        Route::get('/repairs', [\App\Http\Controllers\Admin\InventoryController::class, 'repairs'])->name('admin.inventory.repairs');
        Route::get('/repairs/create', [\App\Http\Controllers\Admin\InventoryController::class, 'createRepair'])->name('admin.inventory.repairs.create');
        Route::post('/repairs/store', [\App\Http\Controllers\Admin\InventoryController::class, 'storeRepair'])->name('admin.inventory.repairs.store');
        Route::post('/repairs/update/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'updateRepairStatus'])->name('admin.inventory.repairs.update');

        // Damages
        Route::get('/damages', [\App\Http\Controllers\Admin\InventoryController::class, 'damages'])->name('admin.inventory.damages');
        Route::get('/damages/create', [\App\Http\Controllers\Admin\InventoryController::class, 'createDamage'])->name('admin.inventory.damages.create');
        Route::post('/damages/store', [\App\Http\Controllers\Admin\InventoryController::class, 'storeDamage'])->name('admin.inventory.damages.store');
        Route::post('/damages/update/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'updateDamage'])->name('admin.inventory.damages.update');
    });
});


Route::get('/admin/check-ticket', function(){
    if(auth()->user()->role != 'admin'){
        return response()->json([
            'ticket_id' => 0
        ]);
    }

    $ticket = Ticket::latest()->first();
    return response()->json([
        'ticket_id' => $ticket?->id ?? 0,
        'message' => $ticket
            ? 'Ticket baru dari '.$ticket->nama
            : 'Tidak ada ticket'
    ]);
})->middleware('auth');

Route::get('/notification/{id}', function($id){
    $notification = auth()->user()->notifications()->findOrFail($id);
    // tandai sudah dibaca
    $notification->markAsRead();

    $notification->delete(); // langsung hapus

    // cek apakah ada URL
    $url = $notification->data['url'] ?? '/dashboard';

    // redirect aman
    return redirect($url);
    });

    Route::get('/notifications/read', function(){
    auth()->user()->unreadNotifications->markAsRead();
    return back();
    });
    

    Route::delete('/notifications/clear', function () {
    auth()->user()->notifications()->delete(); // hapus semua
    return back()->with('success', 'Notifikasi berhasil dibersihkan');
    })->middleware('auth');

require __DIR__.'/auth.php';