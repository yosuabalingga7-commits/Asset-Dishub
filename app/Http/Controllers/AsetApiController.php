<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Asset; // Import Model Asset
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AsetApiController extends Controller
{
    /**
     * Mengambil semua data aset asli dari database untuk peta GIS.
     * Endpoint ini dipanggil oleh JavaScript di halaman Dashboard/GIS.
     */
    public function getAllAssets()
    {
        try {
            // 1. Ambil semua data dari database menggunakan Model Asset
            $assets = Asset::all();

            // 2. Kita rapikan datanya (Mapping) agar sesuai dengan kebutuhan peta
            $formattedAssets = $assets->map(function ($item) {
                
                // Tentukan warna marker berdasarkan status aset
                $warna = '#16a34a'; // Default Hijau (Baik)
                if ($item->status == 'Rusak') {
                    $warna = '#fbbf24';  // Kuning/Oranye
                } elseif ($item->status == 'Kritis') {
                    $warna = '#dc2626'; // Merah
                } elseif ($item->status == 'Proses') {
                    $warna = '#3b82f6'; // Biru
                }

                return [
                    'id'          => $item->id,
                    'id_asset'    => $item->id_asset,
                    'nama'        => $item->nama,
                    'kategori'    => $item->kategori,
                    'jenis'       => $item->jenis,
                    'icon_marker' => $item->icon_marker, // Ikon emoji (💡, 🚦, dll)
                    'alamat'      => $item->alamat,
                    'lat'         => (float) $item->lat, // Pastikan jadi angka desimal
                    'lng'         => (float) $item->lng, // Pastikan jadi angka desimal
                    'status'      => $item->status,
                    'warna'       => $warna,
                    'update'      => $item->updated_at ? $item->updated_at->format('d M Y') : '-',
                    // Cek apakah ada foto di storage, jika tidak pakai gambar default
                    'foto'        => $item->foto ? asset('storage/' . $item->foto) : asset('img/default-asset.png'),
                    'catatan'     => $item->catatan
                ];
            });

            // 3. Kirim data dalam format JSON ke Frontend (Peta)
            return response()->json($formattedAssets, 200);

        } catch (\Exception $e) {
            // Jika terjadi error, kirim pesan errornya
            return response()->json([
                'message' => 'Gagal mengambil data aset dari database',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}