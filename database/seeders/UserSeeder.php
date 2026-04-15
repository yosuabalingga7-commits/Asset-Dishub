<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Kepala Seksi / Monitoring
        // Login: seksi_dishub / password123
        User::updateOrCreate(
            ['username' => 'seksi_dishub'],
            [
                'name' => 'Kepala Seksi Dishub',
                'email' => 'seksi@dishub.test',
                'password' => Hash::make('password123'),
                'role' => 'seksi',
                'no_wa' => '081122334466',
                'foto' => null,
                'is_active' => true,
            ]
        );

        // 2. Akun Petugas Lapangan (Surveyor 01)
        // Login: petugas01 / password123
        User::updateOrCreate(
            ['username' => 'petugas01'],
            [
                'name' => 'Budi Petugas Lapangan',
                'username' => 'petugas01',
                'email' => 'petugas@dishub.test',
                'password' => Hash::make('password123'),
                'role' => 'petugas',
                'no_wa' => '081122334477',
                'foto' => null,
                'is_active' => true,
            ]
        );

        // 3. Akun Petugas Lapangan (Surveyor 02)
        // Login: petugas02 / password123
        User::updateOrCreate(
            ['username' => 'petugas02'],
            [
                'name' => 'Asep Surveyor',
                'email' => 'asep@dishub.test',
                'password' => Hash::make('password123'),
                'role' => 'petugas',
                'no_wa' => '081122334488',
                'foto' => null,
                'is_active' => true,
            ]
        );
    }
}