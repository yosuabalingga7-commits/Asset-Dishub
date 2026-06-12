<?php

namespace App\Models;

use App\Traits\HasSpatialCoordinates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Asset extends Model
{
    use HasFactory, HasSpatialCoordinates;

    protected $table = 'assets';

    protected $fillable = [
        'category_id',
        'id_asset', 
        'nama', 
        'kategori', 
        'jenis', 
        'icon_marker', 
        'merk', 
        'status', 
        'tgl_pemasangan', 
        'lat', 
        'lng', 
        'alamat', 
        'foto', 
        'catatan',
        'foto_terakhir',
        'catatan_terakhir',
        'user_id'
    ];

    protected $casts = [
        'tgl_pemasangan' => 'date', 
    ];

    // tempLatLng is declared in HasSpatialCoordinates trait

    protected $appends = ['foto_url', 'lat', 'lng', 'nama_aset'];

    public function getNamaAsetAttribute(): string
    {
        return $this->nama;
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://via.placeholder.com/800x400?text=No+Image';
    }

    public function setIdAssetAttribute(string $value): void
    {
        $this->attributes['id_asset'] = strtoupper($value);
    }

    /**
     * Relasi ke Model Category
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Relasi ke Model User (Creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope untuk mencari SEMUA aset dalam radius tertentu
     */
    public function scopeWithinRadius(Builder $query, float $lat, float $lng, int $radius = 100)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) <= ?',
                [$lng, $lat, $radius]
            )
            ->orderBy('distance', 'asc');
    }

    /**
     * Scope untuk mencari aset terdekat berdasarkan koordinat
     */
    public function scopeNearest(Builder $query, float $lat, float $lng, int $radius = 5)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) <= ?',
                [$lng, $lat, $radius]
            )
            ->orderBy('distance', 'asc')
            ->limit(1);
    }

    /**
     * Scope untuk mencari aset terdekat yang TERSEDIA (status 'Baik')
     */
    public function scopeNearestAvailable(Builder $query, float $lat, float $lng, int $radius = 5)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_DistanceSphere(coordinates, ST_SetSRID(ST_MakePoint(?, ?), 4326)) <= ?',
                [$lng, $lat, $radius]
            )
            ->where('status', 'Baik')
            ->orderBy('distance', 'asc')
            ->limit(1);
    }

    /**
     * Cek apakah aset tersedia untuk dilaporkan
     */
    public function isAvailableForReport(): bool
    {
        return $this->status === 'Baik';
    }

    /**
     * Cek apakah aset sedang dalam proses perbaikan
     */
    public function isUnderMaintenance(): bool
    {
        return in_array($this->status, ['Rusak', 'Kritis', 'Proses Perbaikan']);
    }
}