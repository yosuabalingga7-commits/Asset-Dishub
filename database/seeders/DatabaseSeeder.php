<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. MEMBUAT DATA MASTER SEKSI (BIDANG)
        // Ini wajib ada agar relasi seksi_id di tabel users tidak error
        $daftar_seksi = [
            ['id' => 1, 'nama_seksi' => 'Penerangan Jalan Umum (PJU)'],
            ['id' => 2, 'nama_seksi' => 'Perlengkapan Jalan'],
            ['id' => 3, 'nama_seksi' => 'Fasilitas Lalu Lintas'],
            ['id' => 4, 'nama_seksi' => 'Pengendalian & Pengawasan'],
            ['id' => 5, 'nama_seksi' => 'Prasarana Transportasi'],
        ];

        foreach ($daftar_seksi as $seksi) {
            Seksi::updateOrCreate(['id' => $seksi['id']], $seksi);
        }

        // 2. MEMBUAT/UPDATE AKUN SUPER ADMIN (AKUN UTAMA)
        User::updateOrCreate(
            ['nip' => '12345678'], // NIP Ketua untuk Login
            [
                'name' => 'Yosua Balingga', 
                'email' => 'admin@kbb.go.id',
                'password' => Hash::make('password123'), 
                'role' => 'super_admin',
                'no_wa' => '6281234567890',
                'foto' => null,
                'status' => 'aktif', // Ditambahkan agar sinkron dengan kolom baru
                'is_active' => true,
            ]
        );

        // 3. MEMBUAT/UPDATE AKUN SEKSI (UNTUK TES TUGAS LAPANGAN)
        User::updateOrCreate(
            ['nip' => '19850101'], 
            [
                'name' => 'Petugas Seksi Lapangan (PJU)',
                'email' => 'seksi@kbb.go.id',
                'password' => Hash::make('password123'), 
                'role' => 'seksi',
                'seksi_id' => 1, // Mengacu ke PJU
                'no_wa' => '628129876543',
                'status' => 'aktif',
                'is_active' => true,
            ]
        );

        // 4. MEMANGGIL SEEDER LAINNYA (JIKA ADA)
        $this->call([
            // Tambahkan seeder lain di sini jika sudah buat file-nya
        ]);
    }
}