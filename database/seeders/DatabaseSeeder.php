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
        // 1. Membuat User Admin agar bisa Login
        // Menggunakan updateOrCreate agar tidak double kalau dijalankan berkali-kali
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Dishub KBB',
                'password' => Hash::make('password'), 
                'role' => 'admin', 
            ]
        );

        // 2. Memanggil Seeder lainnya
        // CATATAN: Pastikan filenya SUDAH ADA di folder seeders sebelum diaktifkan
        $this->call([
            UserSeeder::class, // Ini tadi sudah ada filenya, jadi AMAN
            
            // Hapus tanda // di bawah ini HANYA JIKA filenya sudah kamu buat:
            // CategorySeeder::class, 
            // AssetSeeder::class, 
            // MaintenanceSeeder::class, 
        ]);
    }
}