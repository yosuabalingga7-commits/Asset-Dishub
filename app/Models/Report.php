<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

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

    protected $tempLatLng = [];

    protected $appends = ['lat', 'lng'];

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

        // FALLBACK: Cek apakah data mentah adalah teks WKT langsung (e.g., 'POINT(107.5 -6.8)')
        if (is_string($wkb) && preg_match('/POINT\s*\(\s*([-\d.]+)\s+([-\d.]+)\s*\)/i', $wkb, $matches)) {
            return ['lat' => (float)$matches[2], 'lng' => (float)$matches[1]];
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