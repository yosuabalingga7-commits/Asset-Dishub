<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin (cukup 1)
        User::updateOrCreate(
            ['nip' => '198001012005011001'],
            [
                'name' => 'Admin Dishub',
                'nip' => '198001012005011001',
                'email' => 'admin@dishub.test',
                'password' => Hash::make('password123'),
                'password_plain' => 'password123',
                'role' => 'admin',
                'no_wa' => '081122334411',
                'foto' => null,
                'status' => 'aktif',
                'is_active' => true,
            ]
        );
    }
}