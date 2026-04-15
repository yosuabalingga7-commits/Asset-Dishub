<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Membuat User Admin Utama (Akun Ketua)
        // Login: admin / password
        User::updateOrCreate(
            ['username' => 'admin'], // Kunci unik agar tidak double
            [
                'name' => 'Admin Utama LINTAS',
                'email' => 'admin@kbb.go.id',
                'password' => Hash::make('password'), 
                'role' => 'super_admin',
                'no_wa' => '08123456789',
                'foto' => null,
                'is_active' => true,
            ]
        );

        // 2. Memanggil Seeder lainnya untuk data tambahan
        $this->call([
            UserSeeder::class, 
            // Tambahkan seeder lain di bawah sini jika sudah ada filenya:
            // CategorySeeder::class,
            // AssetSeeder::class,
        ]);
    }
}