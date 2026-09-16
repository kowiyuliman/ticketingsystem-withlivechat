<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // skip jika kosong
        if (empty($row['username']) || empty($row['name'])) {
            return null;
        }

        $username = strtolower(trim($row['username']));

        // cek username unik
        if (User::where('username', $username)->exists()) {
            return null;
        }

        $role = $row['role'] ?? 'user';
        if ($role === 'leader') {
            $role = 'user';
        }

        return new User([
            'name' => $row['name'],
            'username' => $username,
            'password' => Hash::make($row['password'] ?? '123456'),
            'role' => $role,
        ]);
    }
}
