<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'name' => 'Admin Aplikasi',
            'email' => 'admin@dishub.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. Akun Petugas Lapangan
        User::create([
            'name' => 'Budi Petugas',
            'email' => 'petugas@dishub.test',
            'password' => Hash::make('password123'),
            'role' => 'petugas',
        ]);

        // 3. Akun Monitoring Dinas
        User::create([
            'name' => 'Kepala Dinas',
            'email' => 'dinas@dishub.test',
            'password' => Hash::make('password123'),
            'role' => 'dinas',
        ]);
    }
}