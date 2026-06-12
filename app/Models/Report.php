<?php

namespace App\Models;

use App\Traits\HasSpatialCoordinates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory, HasSpatialCoordinates;

    protected $table = 'reports';

    protected $fillable = [
        'ticket_number',
        'source',
        'nama_pelapor',
        'kontak_pelapor',
        'nama_petugas',
        'nip',
        'no_wa',
        'judul_laporan',
        'deskripsi_keluhan',
        'deskripsi',
        'kondisi_aset',
        'alamat',
        'lokasi_koordinat',
        'foto',
        'status',
        'is_validated',
        'kepemilikan',
        'catatan_admin',
        'id_asset',
        'asset_id',
        'ip_address',
    ];

    protected $casts = [
        'is_validated' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // tempLatLng is declared in HasSpatialCoordinates trait

    protected $appends = ['lat', 'lng'];

    /**
     * Relasi ke tabel Asset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relasi ke logs/history perbaikan
     */
    public function logs()
    {
        return $this->hasMany(ReportLog::class, 'report_id');
    }

    /**
     * Relasi ke MaintenanceTicket
     */
    public function maintenance()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    public function maintenanceTicket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }

    public function tiket()
    {
        return $this->hasOne(MaintenanceTicket::class, 'report_id');
    }
}