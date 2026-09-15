<?php

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('management role (boss) can access dashboard and view organization metrics', function () {
    $boss = User::factory()->create([
        'name' => 'Direktur Utama',
        'username' => 'direktur',
        'role' => 'management',
    ]);

    $response = $this->actingAs($boss)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard IT Support');
    $response->assertSee('Tren Tiket Harian');
    $response->assertSee('Grafik Tiket Bulanan');
});

test('management role can fetch realtime dashboard metrics and charts', function () {
    $boss = User::factory()->create([
        'name' => 'Executive Boss',
        'username' => 'boss',
        'role' => 'management',
    ]);

    Ticket::create([
        'ticket_code' => 'MPTB-IT-' . date('Ymd') . '-HW-001',
        'user_id' => $boss->id,
        'nama' => 'Staff A',
        'nomor_laptop' => 'LP-001',
        'kategori' => 'hardware',
        'deskripsi' => 'Printer error',
        'status' => 'open',
    ]);

    $response = $this->actingAs($boss)->get(route('admin.dashboard.realtime'));

    $response->assertOk();
    $data = $response->json();
    expect($data['total'])->toBe(1);
    expect($data['open'])->toBe(1);
    expect(count($data['daily_labels']))->toBe(14);
});

test('management role is redirected directly to dashboard on login', function () {
    $boss = User::factory()->create([
        'username' => 'boss.executive',
        'password' => bcrypt('password123'),
        'role' => 'management',
    ]);

    $response = $this->post('/login', [
        'username' => 'boss.executive',
        'password' => 'password123',
    ]);

    $response->assertRedirect('/admin/dashboard');
});

test('management role cannot access admin management and inventory modification routes', function () {
    $boss = User::factory()->create([
        'username' => 'boss',
        'role' => 'management',
    ]);

    // Management admin route
    $resAdmin = $this->actingAs($boss)->get(route('admin.admins.index'));
    $resAdmin->assertForbidden();

    // Inventory assets create route
    $resInventory = $this->actingAs($boss)->get(route('admin.inventory.assets.create'));
    $resInventory->assertForbidden();
});

test('admin can create user with role management in user management', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/users/store', [
        'name' => 'General Manager',
        'username' => 'gm.management',
        'password' => 'password123',
        'role' => 'management',
    ]);

    $response->assertRedirect('/admin/users');
    $this->assertDatabaseHas('users', [
        'name' => 'General Manager',
        'username' => 'gm.management',
        'role' => 'management',
    ]);
});

