<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMasyarakat extends Model
{
    use HasFactory;

    protected $table = 'laporan_masyarakats';

    protected $fillable = [
        'ticket_number',
        'nama_pelapor',
        'kontak_pelapor',
        'judul_laporan',
        'deskripsi_keluhan',
        'kondisi_aset',
        'lat',
        'lng',
        'alamat',
        'lokasi_koordinat',
        'foto',
        'status',
        'is_validated',
        'kepemilikan',
        'catatan_admin',
        'id_asset',
        'sumber_laporan',
        'ip_address',
    ];

    /**
     * Relasi ke tabel Asset
     * Digunakan untuk mengambil data aset (nama, kategori, dll) dari laporan
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function logs()
    {
        return $this->hasMany(PengaduanLog::class, 'pengaduan_id');
    }

    public function maintenance()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    /**
     * Relasi ini sangat penting untuk MaintenanceController@index
     * Digunakan pada: LaporanMasyarakat::whereDoesntHave('maintenanceTicket')
     */
    public function maintenanceTicket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    // Tambahkan alias 'tiket' agar konsisten dengan model Pengaduan
    public function tiket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }
}