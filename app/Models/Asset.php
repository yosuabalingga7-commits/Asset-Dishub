<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Asset extends Model
{
    use HasFactory;

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
        'lat' => 'double',
        'lng' => 'double',
        'tgl_pemasangan' => 'date', 
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://via.placeholder.com/800x400?text=No+Image';
    }

    public function setIdAssetAttribute($value)
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
     * Mengembalikan semua aset (bukan hanya 1) yang jaraknya <= radius
     *
     * @param Builder $query
     * @param float $lat Latitude pelapor
     * @param float $lng Longitude pelapor
     * @param int $radius Radius pencarian dalam meter
     * @return Builder
     */
    public function scopeWithinRadius(Builder $query, float $lat, float $lng, int $radius = 100)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?',
                [$lng, $lat, $radius]
            )
            ->orderBy('distance', 'asc');
    }

    /**
     * Scope untuk mencari aset terdekat berdasarkan koordinat
     * Menggunakan ST_Distance_Sphere untuk akurasi tinggi dalam radius meter
     *
     * @param Builder $query
     * @param float $lat Latitude pelapor
     * @param float $lng Longitude pelapor
     * @param int $radius Radius pencarian dalam meter (default: 5 meter)
     * @return Builder
     */
    public function scopeNearest(Builder $query, float $lat, float $lng, int $radius = 5)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?',
                [$lng, $lat, $radius]
            )
            ->orderBy('distance', 'asc')
            ->limit(1);
    }

    /**
     * Scope untuk mencari aset terdekat yang TERSEDIA (status 'Baik')
     * Hanya mengembalikan aset dengan status 'Baik' yang bisa dilaporkan
     *
     * @param Builder $query
     * @param float $lat Latitude pelapor
     * @param float $lng Longitude pelapor
     * @param int $radius Radius pencarian dalam meter
     * @return Builder
     */
    public function scopeNearestAvailable(Builder $query, float $lat, float $lng, int $radius = 5)
    {
        return $query
            ->select('*')
            ->selectRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance',
                [$lng, $lat]
            )
            ->whereRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?',
                [$lng, $lat, $radius]
            )
            ->where('status', 'Baik')
            ->orderBy('distance', 'asc')
            ->limit(1);
    }

    /**
     * Cek apakah aset tersedia untuk dilaporkan
     * Returns true jika status = 'Baik'
     */
    public function isAvailableForReport(): bool
    {
        return $this->status === 'Baik';
    }

    /**
     * Cek apakah aset sedang dalam proses perbaikan
     * Returns true jika status = 'Rusak', 'Kritis', atau 'Proses Perbaikan'
     */
    public function isUnderMaintenance(): bool
    {
        return in_array($this->status, ['Rusak', 'Kritis', 'Proses Perbaikan']);
    }
}