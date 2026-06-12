<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * HasSpatialCoordinates (Reusable GRASP Trait — DRY Enforcement)
 * ============================================================================
 * Encapsulates all PostGIS WKB (Well-Known Binary) coordinate parsing logic
 * and the virtual lat/lng accessor/mutator contract.
 *
 * Previously duplicated verbatim across:
 *   - App\Models\Asset
 *   - App\Models\Report
 *
 * USAGE: Add `use HasSpatialCoordinates;` to any Eloquent model that maps
 * its geometry to a PostGIS `geography` or `geometry` column named
 * `coordinates`. The model must also add 'lat' and 'lng' to `$appends`.
 *
 * @property float|null $lat  Virtual accessor for latitude
 * @property float|null $lng  Virtual accessor for longitude
 */
trait HasSpatialCoordinates
{
    /**
     * Internal staging buffer for lat/lng before they are serialized
     * into the binary `coordinates` column via updateCoordinatesFromLatLng().
     * This must be initialized as an empty array per model instance.
     */
    protected array $tempLatLng = [];

    // =========================================================================
    // VIRTUAL ACCESSORS (Read: coordinates -> lat/lng floats)
    // =========================================================================

    public function getLatAttribute(): ?float
    {
        if (array_key_exists('lat', $this->tempLatLng)) {
            return (float) $this->tempLatLng['lat'];
        }
        $coords = $this->parseWkbCoordinates();
        return $coords ? (float) $coords['lat'] : null;
    }

    public function getLngAttribute(): ?float
    {
        if (array_key_exists('lng', $this->tempLatLng)) {
            return (float) $this->tempLatLng['lng'];
        }
        $coords = $this->parseWkbCoordinates();
        return $coords ? (float) $coords['lng'] : null;
    }

    // =========================================================================
    // VIRTUAL MUTATORS (Write: lat/lng floats -> coordinates binary)
    // =========================================================================

    public function setLatAttribute(mixed $value): void
    {
        $this->tempLatLng['lat'] = $value;
        $this->syncCoordinatesColumn();
    }

    public function setLngAttribute(mixed $value): void
    {
        $this->tempLatLng['lng'] = $value;
        $this->syncCoordinatesColumn();
    }

    // =========================================================================
    // INTERNAL: Sync buffer -> PostGIS ST_GeomFromText column
    // =========================================================================

    /**
     * Writes the staged lat/lng buffer into the `coordinates` column
     * using PostGIS ST_GeomFromText with SRID 4326 (WGS84).
     * Only fires when both lat and lng are present in the buffer.
     */
    protected function syncCoordinatesColumn(): void
    {
        $lat = $this->tempLatLng['lat'] ?? null;
        $lng = $this->tempLatLng['lng'] ?? null;

        if ($lat !== null && $lng !== null) {
            $this->attributes['coordinates'] = DB::raw(
                "ST_GeomFromText('POINT($lng $lat)', 4326)"
            );
        }
    }

    // =========================================================================
    // INTERNAL: Deserialize PostGIS EWKB hex -> ['lat' => float, 'lng' => float]
    // =========================================================================

    /**
     * Parses the raw PostGIS EWKB (Extended Well-Known Binary) value stored
     * in the `coordinates` column back into a PHP associative array.
     *
     * Handles:
     *  - Resource streams (pgsql binary mode)
     *  - Raw hex strings (pgsql text mode)
     *  - Both little-endian (01) and big-endian (00) byte orders
     *  - SRID-embedded geometries (EWKB flag 0x20000000)
     *
     * @return array{lat: float, lng: float}|null
     */
    protected function parseWkbCoordinates(): ?array
    {
        if (!isset($this->attributes['coordinates'])) {
            return null;
        }

        $wkb = $this->attributes['coordinates'];

        // Resolve PHP resource stream (PostgreSQL binary transfer mode)
        if (is_resource($wkb)) {
            $wkb = stream_get_contents($wkb);
        }

        if (empty($wkb)) {
            return null;
        }

        // Normalize to hex string if still in raw binary
        if (!ctype_xdigit($wkb)) {
            $wkb = bin2hex($wkb);
        }

        // ── Parse EWKB Header ──
        $byteOrder   = substr($wkb, 0, 2);
        $isLittle    = ($byteOrder === '01');
        $typeHex     = substr($wkb, 2, 8);
        $typeVal     = hexdec(
            $isLittle
                ? strrev(implode('', str_split($typeHex, 2)))
                : $typeHex
        );

        // Offset: 1 (byte order) + 4 (type) = 5 bytes = 10 hex chars
        // If SRID flag set (EWKB), skip additional 4 bytes (8 hex chars)
        $offset = 10;
        if ($typeVal & 0x20000000) {
            $offset += 8;
        }

        // ── Extract X (lng) and Y (lat) as IEEE 754 doubles ──
        $xHex = substr($wkb, $offset, 16);
        $yHex = substr($wkb, $offset + 16, 16);

        if (strlen($xHex) < 16 || strlen($yHex) < 16) {
            return null;
        }

        $xBin = hex2bin($xHex);
        $yBin = hex2bin($yHex);

        if ($isLittle) {
            $xBin = strrev($xBin);
            $yBin = strrev($yBin);
        }

        $x = unpack('d', $xBin)[1]; // Longitude
        $y = unpack('d', $yBin)[1]; // Latitude

        return ['lat' => $y, 'lng' => $x];
    }
}
