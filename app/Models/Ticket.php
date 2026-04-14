<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai dengan file migration: 'tickets'
     */
    protected $table = 'tickets';

    /**
     * Mass assignable attributes.
     * Ditambahkan 'laporan_masyarakat_id' agar terhubung dengan pengaduan warga.
     */
    protected $fillable = [
        'laporan_masyarakat_id', // Menghubungkan ke tabel laporan_masyarakats
        'asset_id', 
        'ticket_number', 
        'reporter_name', 
        'description', 
        'priority', 
        'status', 
        'assigned_to', 
        'assigned_at', 
        'deadline', 
        'progress_percent',
        'foto_sebelum', 
        'foto_sesudah'
    ];

    /**
     * Menghubungkan kolom database ke tipe data tertentu (Casting).
     * 'deadline' dan 'assigned_at' otomatis menjadi objek Carbon agar mudah dimanipulasi tanggalnya.
     */
    protected $casts = [
        'deadline' => 'date',
        'assigned_at' => 'datetime',
        'progress_percent' => 'integer',
        'is_validated' => 'boolean', // Jika nanti digunakan di level tiket
    ];

    /**
     * Relasi ke Laporan Masyarakat (DISHUB Recommendation)
     * Menghubungkan tiket perbaikan dengan asal aduan warga.
     */
    public function laporan()
    {
        return $this->belongsTo(LaporanMasyarakat::class, 'laporan_masyarakat_id');
    }

    /**
     * Relasi ke Aset
     * Menghubungkan tiket dengan aset Dishub (PJU, RPPJ, Marka, dll).
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relasi ke User (Petugas/Teknisi Lapangan)
     * Menghubungkan ke tabel users untuk mengetahui siapa yang ditugaskan.
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope untuk Dashboard: Menampilkan tugas yang diberikan hari ini.
     * Contoh penggunaan: Ticket::hariIni()->get();
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('assigned_at', now()->today());
    }

    /**
     * Scope untuk Status: Mempermudah filter data berdasarkan status tertentu.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}