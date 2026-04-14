<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaporanMasyarakat;
use App\Models\Ticket;
use App\Models\Asset;
use App\Models\User;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil salah satu aset dan user petugas untuk contoh
        $asset = Asset::first();
        $petugas = User::where('role', 'petugas')->first();

        if (!$asset || !$petugas) return;

        // 2. Buat Data Laporan Masyarakat
        LaporanMasyarakat::create([
            'nama_pelapor' => 'Warga Lembang',
            'kontak_pelapor' => '08123456789',
            'judul_laporan' => 'PJU Mati di depan pasar',
            'deskripsi_keluhan' => 'Lampu PJU padam sudah 3 hari, mohon diperbaiki.',
            'status' => 'masuk'
        ]);

        // 3. Buat Data Tiket Maintenance (Agar muncul di "Petugas Hari Ini")
        Ticket::create([
            'asset_id' => $asset->id,
            'ticket_number' => 'TKT-2026-001',
            'description' => 'Perbaikan rutin lampu LED',
            'priority' => 'Tinggi',
            'status' => 'Dalam Proses',
            'assigned_to' => $petugas->id,
            'assigned_at' => now(), // Tanggal hari ini
            'deadline' => now()->addDays(2),
            'progress_percent' => 50
        ]);
    }
}