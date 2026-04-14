<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMasyarakat extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'laporan_masyarakats';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment)
     * Sudah sinkron dengan kolom baru hasil migration terakhir
     */
    protected $fillable = [
        'ticket_number',     // Nomor unik laporan
        'nama_pelapor',
        'kontak_pelapor',    // WhatsApp/No Telp
        'judul_laporan',     // Apa yang dilaporkan
        'deskripsi_keluhan', // Detail keluhan (Default: '-')
        'kondisi_aset',      // Input warga: Rusak, Hilang, Pindah Tempat, Lainnya
        'lat',
        'lng',
        'alamat',            // Teks Alamat Lengkap
        'lokasi_koordinat',  // Gabungan lat,lng
        'foto',              // Nama file foto
        'status',            // Status teknis: masuk, Baik, Rusak, Kritis, Proses Perbaikan
        'is_validated',      // Status verifikasi admin (Boolean)
        'kepemilikan',       // Klasifikasi aset: dishub atau umum
        'catatan_admin',     // Alasan penolakan atau catatan validasi
    ];

    /**
     * RELASI KE RIWAYAT AKTIVITAS (LOGS)
     * Menghubungkan ke tabel logs untuk tracking perubahan status
     */
    public function logs()
    {
        // Menghubungkan ke Model PengaduanLog menggunakan kolom 'pengaduan_id'
        return $this->hasMany(PengaduanLog::class, 'pengaduan_id');
    }

    /**
     * RELASI KE TIKET MAINTENANCE
     * Menghubungkan laporan dengan data penugasan petugas/teknisi
     */
    public function maintenance()
    {
        // Menghubungkan ke Model MaintenanceTicket menggunakan foreign key 'report_id'
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    /**
     * ALIAS RELASI UNTUK MAINTENANCE CONTROLLER
     * Ditambahkan untuk memperbaiki error BadMethodCallException
     */
    public function maintenanceTicket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }
}