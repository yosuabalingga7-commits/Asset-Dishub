<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengumuman extends Model
{
    use HasFactory;

    /**
     * Nama tabel
     */
    protected $table = 'pengumumen';

    /**
     * Kolom yang boleh diisi
     */
    protected $fillable = [
        'judul',
        'isi',
        'status',
        'target',
        'penting',
        'tanggal_mulai',
        'tanggal_selesai',
        'lampiran',
        'jenis',
        'created_by'
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'penting' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relasi ke User (pembuat pengumuman)
     */
    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk filter status aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk filter target
     */
    public function scopeTarget($query, $target)
    {
        if ($target && $target !== 'semua') {
            return $query->where('target', $target);
        }
        return $query;
    }

    /**
     * Scope untuk filter jenis pengumuman
     */
    public function scopeJenis($query, $jenis)
    {
        if ($jenis) {
            return $query->where('jenis', $jenis);
        }
        return $query;
    }

    /**
     * Scope untuk filter penting
     */
    public function scopePenting($query)
    {
        return $query->where('penting', true);
    }

    /**
     * Scope untuk filter masih berlaku (tanggal)
     */
    public function scopeBerlaku($query)
    {
        return $query->where(function($q) {
            $q->whereNull('tanggal_selesai')
              ->orWhere('tanggal_selesai', '>=', now());
        });
    }

    /**
     * Get label jenis pengumuman
     */
    public function getLabelJenisAttribute()
    {
        $labels = [
            'perubahan_layanan' => '📋 Perubahan Layanan',
            'info_operasional' => '🚌 Info Operasional',
            'kebijakan_baru' => '📜 Kebijakan Baru',
            'instruksi_petugas' => '⚠️ Instruksi Petugas',
            'info_proyek' => '🏗️ Info Proyek',
            'surat_edaran' => '📧 Surat Edaran'
        ];
        return $labels[$this->jenis] ?? '📢 Pengumuman';
    }

    /**
     * Get label status
     */
    public function getLabelStatusAttribute()
    {
        return $this->status === 'aktif' ? '✅ Aktif' : '📦 Arsip';
    }

    /**
     * Get label target
     */
    public function getLabelTargetAttribute()
    {
        $labels = [
            'semua' => '👥 Semua',
            'petugas_lapangan' => '👮 Petugas Lapangan',
            'kepala_seksi' => '👔 Kepala Seksi',
            'admin' => '🖥️ Admin'
        ];
        return $labels[$this->target] ?? $this->target;
    }
}