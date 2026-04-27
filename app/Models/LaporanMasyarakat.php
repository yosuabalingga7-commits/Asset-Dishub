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
    ];

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
}