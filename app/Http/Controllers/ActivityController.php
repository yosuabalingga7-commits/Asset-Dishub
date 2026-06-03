<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanMasyarakat;
use App\Models\LaporanPetugas;
use App\Models\Asset;
use App\Models\MaintenanceTicket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
    {
        // Ambil data logs dari database (misalnya dari tabel logs atau dari relasi model)
        // Jika belum ada tabel logs, kita akan mengambil data dari berbagai sumber
        
        $logs = collect();
        
        // 1. Ambil data laporan masyarakat terbaru (CREATE)
        $laporanMasyarakat = LaporanMasyarakat::orderBy('created_at', 'desc')->take(10)->get();
        foreach ($laporanMasyarakat as $item) {
            $logs->push([
                'user'      => $item->nama_pelapor,
                'role'      => 'Masyarakat',
                'type'      => 'CREATE',
                'modul'     => 'LAPORAN MASYARAKAT',
                'risk'      => 'MEDIUM',
                'action'    => 'Membuat Laporan Baru',
                'target'    => '#' . ($item->ticket_number ?? 'LP-' . $item->id),
                'desc'      => 'Masyarakat melaporkan kerusakan: ' . substr($item->judul_laporan, 0, 100),
                'time'      => $item->created_at->format('d M Y - H:i:s'),
                'ip'        => $item->ip_address ?? '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->created_at
            ]);
        }
        
        // 2. Ambil data laporan petugas terbaru (CREATE)
        $laporanPetugas = LaporanPetugas::orderBy('created_at', 'desc')->take(10)->get();
        foreach ($laporanPetugas as $item) {
            $logs->push([
                'user'      => $item->nama_petugas,
                'role'      => 'Petugas Lapangan',
                'type'      => 'CREATE',
                'modul'     => 'LAPORAN PETUGAS',
                'risk'      => 'MEDIUM',
                'action'    => 'Membuat Laporan Teknis',
                'target'    => '#' . ($item->ticket_number ?? 'LP-P-' . $item->id),
                'desc'      => 'Petugas melaporkan temuan: ' . substr($item->judul_laporan, 0, 100),
                'time'      => $item->created_at->format('d M Y - H:i:s'),
                'ip'        => $item->ip_address ?? '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->created_at
            ]);
        }
        
        // 3. Ambil data aset yang baru ditambahkan (CREATE)
        $assets = Asset::orderBy('created_at', 'desc')->take(10)->get();
        foreach ($assets as $item) {
            $logs->push([
                'user'      => 'Admin Sistem',
                'role'      => 'Administrator',
                'type'      => 'CREATE',
                'modul'     => 'MANAJEMEN ASET',
                'risk'      => 'LOW',
                'action'    => 'Menambahkan Aset Baru',
                'target'    => $item->id_asset,
                'desc'      => 'Penambahan aset baru dengan nama: ' . ($item->nama_aset ?? $item->id_asset),
                'time'      => $item->created_at->format('d M Y - H:i:s'),
                'ip'        => '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->created_at
            ]);
        }
        
        // 4. Ambil data aset yang diupdate statusnya
        $assetsUpdated = Asset::where('updated_at', '!=', DB::raw('created_at'))
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        foreach ($assetsUpdated as $item) {
            $logs->push([
                'user'      => 'Admin/Sistem',
                'role'      => 'Administrator',
                'type'      => 'UPDATE',
                'modul'     => 'MANAJEMEN ASET',
                'risk'      => 'LOW',
                'action'    => 'Memperbarui Status Aset',
                'target'    => $item->id_asset,
                'desc'      => 'Status aset diperbarui menjadi: ' . ($item->status ?? 'Tidak diketahui'),
                'time'      => $item->updated_at->format('d M Y - H:i:s'),
                'ip'        => '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->updated_at
            ]);
        }
        
        // 5. Ambil data user yang login (LOGIN) - dari tabel users
        $users = User::orderBy('updated_at', 'desc')->take(10)->get();
        foreach ($users as $item) {
            $logs->push([
                'user'      => $item->name,
                'role'      => ucfirst($item->role ?? 'User'),
                'type'      => 'LOGIN',
                'modul'     => 'AUTHENTIKASI',
                'risk'      => 'LOW',
                'action'    => 'Aktivitas Login',
                'target'    => 'Sistem',
                'desc'      => 'User login ke sistem KBB SMART ASSET',
                'time'      => $item->updated_at->format('d M Y - H:i:s'),
                'ip'        => $item->last_login_ip ?? '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->updated_at
            ]);
        }
        
        // 6. Ambil data maintenance/tiket yang statusnya berubah (UPDATE)
        $maintenances = MaintenanceTicket::orderBy('updated_at', 'desc')->take(10)->get();
        foreach ($maintenances as $item) {
            $logs->push([
                'user'      => 'Petugas',
                'role'      => 'Petugas Lapangan',
                'type'      => 'UPDATE',
                'modul'     => 'MAINTENANCE',
                'risk'      => 'LOW',
                'action'    => 'Memperbarui Status Tiket',
                'target'    => '#' . ($item->ticket_number ?? 'MTC-' . $item->id),
                'desc'      => 'Status tiket maintenance diperbarui menjadi: ' . ($item->status ?? 'Diproses'),
                'time'      => $item->updated_at->format('d M Y - H:i:s'),
                'ip'        => '127.0.0.1',
                'status'    => 'SUCCESS',
                'created_at' => $item->updated_at
            ]);
        }
        
        // Urutkan logs berdasarkan waktu terbaru
        $logs = $logs->sortByDesc('created_at')->values();
        
        // Hitung statistik
        $stats = [
            'total'    => $logs->count(),
            'failed'   => $logs->where('status', 'FAILED')->count(),
            'danger'   => $logs->where('type', 'DELETE')->count(),
            'validate' => $logs->where('type', 'VALIDASI')->count(),
        ];
        
        // Jika ingin pagination manual
        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $currentItems = $logs->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $logs->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        return view('admin.activity.index', compact('logs', 'stats'));
    }
}