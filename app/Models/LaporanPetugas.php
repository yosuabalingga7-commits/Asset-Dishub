<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPetugas extends Model
{
    use HasFactory;

    /**
     * Nama tabel sesuai migration.
     */
    protected $table = 'laporan_petugas';

    /**
     * Mass Assignment.
     * Ditambahkan 'deskripsi' untuk menampung detail teknis dari petugas.
     */
    protected $fillable = [
        'nama_petugas',   // Nama petugas
        'nip',            // NIP petugas
        'no_wa',          // Nomor WhatsApp aktif
        'judul_laporan',  // Nama aset / judul
        'kondisi_aset',   // Status: Rusak, Hilang, Pindah Tempat, Lainnya
        'deskripsi',      // <--- TAMBAHAN BARU: Detail teknis
        'foto',           // Path file gambar
        'lat',            // Latitude GIS
        'lng',            // Longitude GIS
        'status',         // masuk/proses/selesai
    ];

    /**
     * Default values.
     */
    protected $attributes = [
        'status' => 'masuk',
    ];

    /**
     * Relasi ke User berdasarkan NIP.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'nip', 'nip');
    }
}