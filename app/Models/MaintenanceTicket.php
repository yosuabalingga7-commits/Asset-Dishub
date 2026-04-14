<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaintenanceTicket extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'maintenance_tickets';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment)
     * Ditambahkan foto_perbaikan dan completion_notes sesuai migrasi terbaru
     */
    protected $fillable = [
        'ticket_code',
        'report_id',
        'asset_id',
        'user_id',
        'category',
        'kepemilikan',
        'subject',
        'description',
        'location_address',
        'latitude',
        'longitude',
        'status',
        'priority',
        'technician_name',
        'started_at',
        'finished_at',
        'edit_reason',
        'deadline',
        'category_id',
        'jenis_aset',
        'foto_perbaikan',   // Tambahan sinkronisasi migrasi
        'completion_notes', // Tambahan sinkronisasi migrasi
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'deadline'    => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * ACCESSOR: Status Slug
     * Memastikan status selalu konsisten untuk filter Alpine.js di frontend.
     * Mengikuti standar: 'baik', 'proses', 'rusak', 'kritis'
     */
    public function getStatusSlugAttribute()
    {
        $status = strtolower($this->status);

        if (Str::contains($status, ['baik', 'bagus', 'normal'])) {
            return 'baik';
        }
        if (Str::contains($status, ['proses', 'perbaikan', 'progres', 'jalan'])) {
            return 'proses';
        }
        if (Str::contains($status, ['rusak', 'mati', 'error'])) {
            return 'rusak';
        }
        if (Str::contains($status, ['kritis', 'parah', 'darurat'])) {
            return 'kritis';
        }

        return $status;
    }

    /**
     * Relasi ke tabel Laporan Masyarakat
     */
    public function report()
    {
        return $this->belongsTo(LaporanMasyarakat::class, 'report_id');
    }

    /**
     * Relasi ke tabel Assets
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relasi ke tabel Users (Petugas/Seksi)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Belum Ditentukan'
        ]);
    }

    /**
     * Relasi ke tabel MaintenanceLogs
     */
    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'ticket_id');
    }

    /**
     * Helper untuk cek kategori di Blade/Controller
     */
    public function isDishub()
    {
        return strtoupper($this->kepemilikan) === 'DISHUB' || strtolower($this->category) === 'dishub';
    }

    public function isUmum()
    {
        return !$this->isDishub();
    }
}