<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        // DATA LOG DENGAN METADATA AUDIT LENGKAP
        $logs = [
            [
                'user'      => 'Zulkifli Ahmad',
                'role'      => 'Super Admin',
                'type'      => 'DELETE',
                'modul'     => 'TIKET',
                'risk'      => 'HIGH',
                'action'    => 'Menghapus Permanen',
                'target'    => 'Data Tiket #MTC-2026-001',
                'desc'      => 'Penghapusan record tiket maintenance karena kesalahan input lokasi oleh petugas lapangan.',
                'time'      => '16 Feb 2026 - 14:20:15',
                'ip'        => '192.168.1.12',
                'status'    => 'SUCCESS'
            ],
            [
                'user'      => 'Zulkifli Ahmad',
                'role'      => 'Super Admin',
                'type'      => 'LOGIN',
                'modul'     => 'AUTH',
                'risk'      => 'CRITICAL',
                'action'    => 'Percobaan Masuk',
                'target'    => 'Sistem Otentikasi',
                'desc'      => 'Gagal login ke panel admin. Terdeteksi kesalahan input kata sandi sebanyak 3 kali.',
                'time'      => '16 Feb 2026 - 13:45:05',
                'ip'        => '192.168.1.12',
                'status'    => 'FAILED'
            ],
            [
                'user'      => 'Rendi Wijaya',
                'role'      => 'Petugas Lapangan',
                'type'      => 'UPDATE',
                'modul'     => 'TIKET',
                'risk'      => 'LOW',
                'action'    => 'Memperbarui Status',
                'target'    => 'Tiket #MTC-2026-099',
                'desc'      => 'Mengubah status pengerjaan dari "Proses" menjadi "Selesai". Dokumentasi foto terlampir.',
                'time'      => '16 Feb 2026 - 13:05:44',
                'ip'        => '192.168.1.45',
                'status'    => 'SUCCESS'
            ],
            [
                'user'      => 'Santi Putri',
                'role'      => 'Admin Dinas',
                'type'      => 'VALIDASI',
                'modul'     => 'LAPORAN',
                'risk'      => 'MEDIUM',
                'action'    => 'Melakukan Validasi',
                'target'    => 'Laporan Pengaduan #LPR-882',
                'desc'      => 'Verifikasi laporan masyarakat terkait kerusakan PJU di area Jalan Sudirman.',
                'time'      => '16 Feb 2026 - 11:45:10',
                'ip'        => '10.22.4.1',
                'status'    => 'SUCCESS'
            ],
            [
                'user'      => 'Budi Operator',
                'role'      => 'Operator Sistem',
                'type'      => 'CREATE',
                'modul'     => 'ASET',
                'risk'      => 'LOW',
                'action'    => 'Mendaftarkan Data',
                'target'    => 'Aset Baru: Lampu PJU-X9',
                'desc'      => 'Penambahan inventaris aset dinas baru untuk wilayah Kecamatan Timur.',
                'time'      => '15 Feb 2026 - 17:00:22',
                'ip'        => '192.168.1.15',
                'status'    => 'SUCCESS'
            ],
        ];

        // LOGIKA STATISTIK AUDIT (SUMARRY)
        $stats = [
            'total'    => 1240, // Angka simulasi total
            'failed'   => collect($logs)->where('status', 'FAILED')->count(),
            'danger'   => collect($logs)->where('type', 'DELETE')->count(),
            'validate' => collect($logs)->where('type', 'VALIDASI')->count(),
        ];

        return view('admin.activity.index', compact('logs', 'stats'));
    }
}