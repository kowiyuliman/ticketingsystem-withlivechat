<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin can view admin management index page', function () {
    $admin = User::factory()->create([
        'name' => 'Super Admin',
        'username' => 'superadmin',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.admins.index'));

    $response->assertOk();
    $response->assertSee('Management Admin');
    $response->assertSee('Daftar Administrator Sistem');
    $response->assertSee('Super Admin');
    $response->assertSee('superadmin');
});

test('admin can view create admin form with role options', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.admins.create'));

    $response->assertOk();
    $response->assertSee('Tambah Admin Baru');
    $response->assertSee('Role Hak Akses');
    $response->assertSee('Admin / IT Support');
    $response->assertSee('Management / Bos');
});

test('admin can successfully store new admin with role option', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // 1. Store Admin
    $resAdmin = $this->actingAs($admin)->post(route('admin.admins.store'), [
        'name' => 'Ahmad Fauzi, S.T.',
        'username' => 'ahmad.fauzi',
        'password' => 'secret123',
        'role' => 'admin',
    ]);

    $resAdmin->assertRedirect(route('admin.admins.index'));
    $resAdmin->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Ahmad Fauzi, S.T.',
        'username' => 'ahmad.fauzi',
        'role' => 'admin',
    ]);

    // 2. Store Management / Boss
    $resBoss = $this->actingAs($admin)->post(route('admin.admins.store'), [
        'name' => 'Bapak Direktur Utama',
        'username' => 'pak.direktur',
        'password' => 'secret123',
        'role' => 'management',
    ]);

    $resBoss->assertRedirect(route('admin.admins.index'));
    $this->assertDatabaseHas('users', [
        'name' => 'Bapak Direktur Utama',
        'username' => 'pak.direktur',
        'role' => 'management',
    ]);
});

test('admin creation validates unique username and minimum password length', function () {
    $admin = User::factory()->create([
        'username' => 'existing.admin',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.admins.store'), [
        'name' => 'Test Admin',
        'username' => 'existing.admin',
        'password' => '123',
        'role' => 'admin',
    ]);

    $response->assertSessionHasErrors(['username', 'password']);
});

test('admin can update another admin data, role, and password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'old.admin',
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.admins.update', $otherAdmin->id), [
        'name' => 'Updated Name',
        'username' => 'updated.admin',
        'role' => 'management',
        'password' => 'newpassword123',
    ]);

    $response->assertRedirect(route('admin.admins.index'));
    $response->assertSessionHas('success');

    $otherAdmin->refresh();
    expect($otherAdmin->name)->toBe('Updated Name');
    expect($otherAdmin->username)->toBe('updated.admin');
    expect($otherAdmin->role)->toBe('management');
    expect(Hash::check('newpassword123', $otherAdmin->password))->toBeTrue();
});

test('admin cannot delete their own active account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete(route('admin.admins.delete', $admin->id));

    $response->assertRedirect(route('admin.admins.index'));
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('admin can delete another admin account', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $otherAdmin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete(route('admin.admins.delete', $otherAdmin->id));

    $response->assertRedirect(route('admin.admins.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);
});
