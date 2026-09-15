<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    /**
     * Display a listing of admin & management users.
     */
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'management'])->latest()->get();
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin / management user.
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created admin / management in storage.
     */
    public function store(Request $request)
    {
        $username = strtolower(trim($request->username));
        $request->merge(['username' => $username]);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|min:3|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,management',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ini sudah digunakan oleh pengguna lain.',
            'username.min'      => 'Username minimal 3 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'role.required'     => 'Pilihan role akun wajib dipilih.',
            'role.in'           => 'Role yang dipilih tidak valid.',
        ]);

        User::create([
            'name'     => trim($request->name),
            'username' => $username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        $roleLabel = $request->role === 'admin' ? 'Administrator IT' : 'Management / Bos';
        return redirect()->route('admin.admins.index')->with('success', 'Akun ' . $roleLabel . ' "' . $request->name . '" berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified admin / management user.
     */
    public function edit($id)
    {
        $admin = User::whereIn('role', ['admin', 'management'])->findOrFail($id);
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin / management user in storage.
     */
    public function update(Request $request, $id)
    {
        $admin = User::whereIn('role', ['admin', 'management'])->findOrFail($id);

        $username = strtolower(trim($request->username));
        $request->merge(['username' => $username]);

        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|min:3|max:50|unique:users,username,' . $admin->id,
            'password' => 'nullable|string|min:6',
            'role'     => 'required|in:admin,management',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ini sudah terdaftar pada pengguna lain.',
            'username.min'      => 'Username minimal 3 karakter.',
            'password.min'      => 'Password baru minimal 6 karakter.',
            'role.required'     => 'Pilihan role akun wajib dipilih.',
            'role.in'           => 'Role yang dipilih tidak valid.',
        ]);

        $admin->name = trim($request->name);
        $admin->username = $username;
        $admin->role = $request->role;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admin.admins.index')->with('success', 'Data akun "' . $admin->name . '" berhasil diperbarui!');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy($id)
    {
        $admin = User::whereIn('role', ['admin', 'management'])->findOrFail($id);

        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.admins.index')->with('error', 'Anda tidak dapat menghapus akun yang sedang aktif Anda gunakan saat ini!');
        }

        if ($admin->role === 'admin') {
            $totalAdmin = User::where('role', 'admin')->count();
            if ($totalAdmin <= 1) {
                return redirect()->route('admin.admins.index')->with('error', 'Tidak dapat menghapus admin terakhir pada sistem!');
            }
        }

        $name = $admin->name;
        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', 'Akun "' . $name . '" berhasil dihapus!');
    }
}
