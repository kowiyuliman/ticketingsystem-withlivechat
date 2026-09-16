<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // NORMALISASI USERNAME
        $username = strtolower(trim($request->username));

        $request->merge([
            'username' => $username
        ]);

        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,management,user'
        ]);

        User::create([
            'name' => $request->name,
            'username' => $username,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect('/admin/users')->with('success', 'User berhasil dibuat');
    }

    public function edit($id)
    { 
        $user = User::findOrFail($id); 
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$user->id,
            'role' => 'required|in:admin,management,user'
        ]);

        // NORMALISASI USERNAME
        $username = strtolower(trim($request->username));

        $data = [
            'name' => $request->name,
            'username' => $username,
            'role' => $request->role,
        ];

        // password optional
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect('/admin/users')->with('success', 'User berhasil diupdate');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        Excel::import(new UsersImport, $request->file('file'));

        return back()->with('success', 'Import user berhasil');
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = json_decode($request->selected_users, true);
            if (empty($ids)) {
                return back()->with('error', 'Tidak ada user dipilih');
            }

            User::whereIn('id', $ids)
                ->where('id', '!=', auth()->id())
                ->delete();

            return back()->with('success', count($ids) . ' user berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user');
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $user->delete();

        return back()->with('success', 'User dihapus');
    }
}