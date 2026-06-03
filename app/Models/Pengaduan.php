<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    // Nama tabel di database sesuai file migrasi G:\Kerja\Dishub_kbb\database\migrations\2026_03_03_080017_create_pengaduans_table.php
    protected $table = 'pengaduans';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'ticket_number',
        'nama_pelapor',
        'nik',
        'kontak_pelapor',
        'whatsapp',
        'judul_laporan',
        'kategori_aset', 
        'jenis_aset',
        'kondisi_aset',
        'lokasi',
        'alamat_manual',
        'status',
        'deskripsi',
        'catatan_admin',
        'foto',
        'lat',
        'lng',
        'petugas_nama',
        'petugas_nip',
        'petugas_jabatan'
    ];

    /**
     * Relasi ke MaintenanceTicket (Logika 1 Laporan = 1 Tiket)
     * Ditambahkan untuk mendukung PengaduanController->show
     */
    public function tiket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    /**
     * Relasi ke tabel Logs (Riwayat Aktivitas)
     */
    public function logs()
    {
        // Menghubungkan ke Model PengaduanLog
        return $this->hasMany(PengaduanLog::class, 'pengaduan_id');
    }

    /**
     * Boot function untuk generate Ticket Number otomatis
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ticket_number)) {
                // Format: TKT-20260309-ABCD
                $model->ticket_number = 'TKT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            }
        });
    }
}