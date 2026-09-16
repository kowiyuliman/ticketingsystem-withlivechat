<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin can view user management page without leader columns', function () {
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'username' => 'admin',
        'role' => 'admin',
    ]);

    $regularUser = User::factory()->create([
        'name' => 'Staff Regular',
        'username' => 'staff.regular',
        'role' => 'user',
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertSee('User Management');
    $response->assertSee('Staff Regular');
    $response->assertSee('staff.regular');
    $response->assertDontSee('data-role="leader"', false);
});

test('admin can access create user form without leader options', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/users/create');

    $response->assertOk();
    $response->assertSee('Tambah User');
    $response->assertDontSee('<option value="leader">', false);
});

test('admin can create user with role user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/users/store', [
        'name' => 'Budi Santoso',
        'username' => 'budi.santoso',
        'password' => 'secret123',
        'role' => 'user',
    ]);

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'Budi Santoso',
        'username' => 'budi.santoso',
        'role' => 'user',
    ]);
});

test('admin can edit and update an existing user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'name' => 'Old Name',
        'username' => 'old.user',
        'role' => 'user',
    ]);

    $responseEdit = $this->actingAs($admin)->get('/admin/users/edit/' . $user->id);
    $responseEdit->assertOk();
    $responseEdit->assertDontSee('Leader');

    $responseUpdate = $this->actingAs($admin)->post('/admin/users/update/' . $user->id, [
        'name' => 'Updated User Name',
        'username' => 'updated.user',
        'role' => 'user',
    ]);

    $responseUpdate->assertRedirect('/admin/users');
    $responseUpdate->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Updated User Name');
    expect($user->username)->toBe('updated.user');
});

test('admin can delete a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($admin)->delete('/admin/users/delete/' . $user->id);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

