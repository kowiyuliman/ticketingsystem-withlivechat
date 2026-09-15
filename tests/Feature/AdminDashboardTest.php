<?php

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dashboard renders successfully for authenticated user', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard IT Support');
    $response->assertSee('Open Tiket');
    $response->assertSee('On Progress Ticket');
    $response->assertSee('Pending Tiket');
    $response->assertSee('Close Tiket');
    $response->assertSee('Cancel Tiket');
    $response->assertDontSee('Total Ticket');
    $response->assertDontSee('Lihat Detail');
    $response->assertSee('admin/tickets?tab=open');
    $response->assertSee('admin/tickets?tab=progress');
    $response->assertSee('admin/tickets?tab=pending');
    $response->assertSee('admin/tickets?tab=closed');
    $response->assertSee('admin/tickets?tab=cancel');
});

test('admin tickets tabs respond to tab query parameter', function () {
    $user = User::factory()->create(['role' => 'admin']);

    // Tab Open
    $resOpen = $this->actingAs($user)->get('/admin/tickets?tab=open');
    $resOpen->assertOk();
    $resOpen->assertSee('id="open-tab"', false);
    $resOpen->assertSee('class="nav-link active font-weight-bold" id="open-tab"', false);

    // Tab Progress
    $resProg = $this->actingAs($user)->get('/admin/tickets?tab=progress');
    $resProg->assertOk();
    $resProg->assertSee('class="nav-link active font-weight-bold" id="progress-tab"', false);

    // Tab Pending
    $resPend = $this->actingAs($user)->get('/admin/tickets?tab=pending');
    $resPend->assertOk();
    $resPend->assertSee('class="nav-link active font-weight-bold" id="pending-tab"', false);

    // Tab Closed
    $resClose = $this->actingAs($user)->get('/admin/tickets?tab=closed');
    $resClose->assertOk();
    $resClose->assertSee('class="nav-link active font-weight-bold" id="closed-tab"', false);

    // Tab Cancel
    $resCancel = $this->actingAs($user)->get('/admin/tickets?tab=cancel');
    $resCancel->assertOk();
    $resCancel->assertSee('class="nav-link active font-weight-bold" id="cancel-tab"', false);
});

test('admin dashboard realtime endpoint returns complete metrics and chart data', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $asset = Asset::create([
        'type' => 'laptop',
        'brand' => 'Lenovo',
        'serial_number' => 'SN-TEST-99',
        'condition' => 'good',
        'status' => 'assigned',
    ]);

    Ticket::create([
        'ticket_code' => 'MPTB-IT-' . date('Ymd') . '-HW-001',
        'user_id' => $user->id,
        'nama' => 'Budi Santoso',
        'nomor_laptop' => 'LP-TEST-99',
        'serial_number' => $asset->serial_number,
        'kategori' => 'hardware',
        'deskripsi' => 'Layar bergaris',
        'status' => 'open',
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard.realtime'));

    $response->assertOk();
    $response->assertJsonStructure([
        'total',
        'open',
        'progress',
        'pending',
        'closed',
        'cancelled',
        'daily_labels',
        'daily_values',
        'monthly_labels',
        'monthly_values',
        'kategori_labels',
        'kategori_values',
        'workload_labels',
        'workload_values',
        'workload_table',
        'latest_open_tickets',
        'latest_ticket_id',
        'latest_ticket_message',
    ]);

    $data = $response->json();
    expect($data['total'])->toBe(1);
    expect($data['open'])->toBe(1);
    expect(count($data['daily_labels']))->toBe(14);
    expect(count($data['daily_values']))->toBe(14);
    expect(count($data['latest_open_tickets']))->toBe(1);
    expect($data['latest_open_tickets'][0]['nomor_laptop'])->toBe('LP-TEST-99');
});
