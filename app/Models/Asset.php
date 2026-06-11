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
        'tgl_pemasangan' => 'date', 
    ];

    protected $tempLatLng = [];

    protected $appends = ['foto_url', 'lat', 'lng'];

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

    public function getLatAttribute()
    {
        if (array_key_exists('lat', $this->tempLatLng)) {
            return (float) $this->tempLatLng['lat'];
        }
        $coords = $this->parseCoordinates();
        return $coords ? (float) $coords['lat'] : null;
    }

    public function getLngAttribute()
    {
        if (array_key_exists('lng', $this->tempLatLng)) {
            return (float) $this->tempLatLng['lng'];
        }
        $coords = $this->parseCoordinates();
        return $coords ? (float) $coords['lng'] : null;
    }

    public function setLatAttribute($value)
    {
        $this->tempLatLng['lat'] = $value;
        $this->updateCoordinatesFromLatLng();
    }

    public function setLngAttribute($value)
    {
        $this->tempLatLng['lng'] = $value;
        $this->updateCoordinatesFromLatLng();
    }

    protected function updateCoordinatesFromLatLng()
    {
        $lat = $this->tempLatLng['lat'] ?? $this->lat;
        $lng = $this->tempLatLng['lng'] ?? $this->lng;

        if ($lat !== null && $lng !== null) {
            $this->attributes['coordinates'] = \DB::raw("ST_GeomFromText('POINT($lng $lat)', 4326)");
        }
    }

    protected function parseCoordinates()
    {
        if (!isset($this->attributes['coordinates'])) {
            return null;
        }

        $wkb = $this->attributes['coordinates'];
        
        if (is_resource($wkb)) {
            $wkb = stream_get_contents($wkb);
        }

        if (empty($wkb)) {
            return null;
        }

        if (!ctype_xdigit($wkb)) {
            $wkb = bin2hex($wkb);
        }

        $byteOrder = substr($wkb, 0, 2);
        $isLittleEndian = ($byteOrder === '01');

        $type = substr($wkb, 2, 8);
        $typeVal = hexdec($isLittleEndian ? strrev(implode('', str_split($type, 2))) : $type);
        $hasSRID = ($typeVal & 0x20000000);
        
        $offset = 10;
        if ($hasSRID) {
            $offset += 8;
        }

        $xHex = substr($wkb, $offset, 16);
        $yHex = substr($wkb, $offset + 16, 16);

        if (strlen($xHex) < 16 || strlen($yHex) < 16) {
            return null;
        }

        $xBin = hex2bin($xHex);
        $yBin = hex2bin($yHex);

        if ($isLittleEndian) {
            $xBin = strrev($xBin);
            $yBin = strrev($yBin);
        }

        $x = unpack('d', $xBin)[1];
        $y = unpack('d', $yBin)[1];

        return ['lat' => $y, 'lng' => $x];
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