<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AsetApiController extends Controller
{
    /**
     * Mengambil data aset dari database untuk peta GIS dengan optimasi spasial.
     * Menerapkan dinamika penyaringan Bounding Box (BBOX) dan Clustering level DB.
     */
    public function getAllAssets(Request $request)
    {
        try {
            // 1. Tangkap parameter spasial dari request frontend
            $minLat = $request->input('minLat');
            $minLng = $request->input('minLng');
            $maxLat = $request->input('maxLat');
            $maxLng = $request->input('maxLng');
            $zoom   = (int) $request->input('zoom', 12);

            // Deteksi jenis driver database untuk Protected Variations (fallback aman)
            $dbDriver = DB::connection()->getDriverName();
            $isPostGis = ($dbDriver === 'pgsql');

            // Validasi apakah kueri spasial BBOX aktif digunakan
            $hasBbox = ($minLat !== null && $minLng !== null && $maxLat !== null && $maxLng !== null);

            // 2. Evaluasi Strategi Pengambilan Data (Information Expert)
            if ($isPostGis && $hasBbox) {

                // Tentukan grid size untuk clustering PostGIS berdasarkan level Zoom makro
                $gridSize = null;
                if ($zoom < 10) {
                    $gridSize = 0.08; // Akurasi grid ~8.8 km
                } elseif ($zoom >= 10 && $zoom < 12) {
                    $gridSize = 0.02; // Akurasi grid ~2.2 km
                }

                // JIKA ZOOM MAKRO: Eksekusi Server-Side Clustering (ST_SnapToGrid)
                if ($gridSize !== null) {
                    $clusteredData = DB::table('assets')
                        ->selectRaw('COUNT(id) as asset_count')
                        ->selectRaw('ST_Y(ST_Centroid(ST_Collect(coordinates))) as lat')
                        ->selectRaw('ST_X(ST_Centroid(ST_Collect(coordinates))) as lng')
                        ->selectRaw('MIN(kategori) as dominant_category')
                        ->selectRaw('MIN(icon_marker) as default_icon')
                        ->whereRaw("ST_Contains(ST_MakeEnvelope(?, ?, ?, ?, 4326), coordinates)", [
                            (float) $minLng,
                            (float) $minLat,
                            (float) $maxLng,
                            (float) $maxLat
                        ])
                        ->groupByRaw("ST_SnapToGrid(coordinates, ?)", [$gridSize])
                        ->get();

                    $formattedClusters = $clusteredData->map(function ($cluster) {
                        return [
                            'id'          => 'cluster-' . md5($cluster->lat . '-' . $cluster->lng),
                            'is_cluster'  => true,
                            'count'       => (int) $cluster->asset_count,
                            'nama'        => $cluster->asset_count . ' Aset (' . $cluster->dominant_category . ')',
                            'kategori'    => 'Klaster Spasial',
                            'jenis'       => 'Aset Teragregasi',
                            'icon_marker' => $cluster->asset_count > 30 ? '🌀' : '📦',
                            'alamat'      => 'Area Klaster Spasial KBB',
                            'lat'         => (float) $cluster->lat,
                            'lng'         => (float) $cluster->lng,
                            'status'      => 'Proses',
                            'warna'       => '#3b82f6', // Biru untuk menandakan klaster dinamis
                            'update'      => '-',
                            'foto'        => asset('img/default-asset.png'),
                            'catatan'     => 'Data diakumulasikan secara dinamis untuk menghemat pemakaian memori browser.'
                        ];
                    });

                    return response()->json($formattedClusters, 200);
                }

                // JIKA ZOOM MIKRO: Ambil Data Individu hanya di dalam Viewport (ST_Contains)
                $assets = Asset::select('id', 'id_asset', 'nama', 'kategori', 'jenis', 'icon_marker', 'alamat', 'status', 'foto', 'catatan', 'updated_at')
                    ->selectRaw('ST_Y(coordinates) as calculated_lat')
                    ->selectRaw('ST_X(coordinates) as calculated_lng')
                    ->whereRaw("ST_Contains(ST_MakeEnvelope(?, ?, ?, ?, 4326), coordinates)", [
                        (float) $minLng,
                        (float) $minLat,
                        (float) $maxLng,
                        (float) $maxLat
                    ])
                    ->get();
            } else {
                // FALLBACK: Kueri tanpa parameter BBOX (atau untuk lingkungan non-PostgreSQL)
                // Membaca koordinat melalui model accessor bawaan yang ter-cached
                $assets = Asset::all();
            }

            // 3. Serialisasi Data Terformat
            $formattedAssets = $assets->map(function ($item) use ($isPostGis, $hasBbox) {
                // Tentukan warna penanda kondisi visual peta
                $warna = '#16a34a'; // Default Hijau (Baik)
                if ($item->status == 'Rusak') {
                    $warna = '#fbbf24';  // Oranye/Kuning
                } elseif ($item->status == 'Kritis') {
                    $warna = '#dc2626'; // Merah
                } elseif ($item->status == 'Proses Perbaikan' || $item->status == 'Proses') {
                    $warna = '#3b82f6'; // Biru
                }

                // Resolusi koordinat berdasar teknik kueri yang dieksekusi
                $lat = ($isPostGis && $hasBbox) ? (float) $item->calculated_lat : (float) $item->lat;
                $lng = ($isPostGis && $hasBbox) ? (float) $item->calculated_lng : (float) $item->lng;

                return [
                    'id'          => $item->id,
                    'id_asset'    => $item->id_asset,
                    'nama'        => $item->nama,
                    'kategori'    => $item->kategori,
                    'jenis'       => $item->jenis,
                    'icon_marker' => $item->icon_marker ?? '📍',
                    'alamat'      => $item->alamat,
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'status'      => $item->status,
                    'warna'       => $warna,
                    'update'      => $item->updated_at ? $item->updated_at->format('d M Y') : '-',
                    'foto'        => $item->foto ? asset('storage/' . $item->foto) : asset('img/default-asset.png'),
                    'catatan'     => $item->catatan
                ];
            });

            return response()->json($formattedAssets, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyaring data spasial dari database',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
