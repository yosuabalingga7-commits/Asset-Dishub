<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManajemenController extends Controller
{
    /**
     * Tampilan Utama GIS (Halaman Monitoring)
     * Sinkron dengan Database map_settings
     */
    public function gis()
    {
        $setting = DB::table('map_settings')->first();
        $assets = Asset::all(); 

        $mapConfig = [
            'center'      => [
                (float)($setting->latitude ?? -6.8431), 
                (float)($setting->longitude ?? 107.4912)
            ], 
            'defaultZoom' => (int)($setting->zoom ?? 11), 
            'detailZoom'  => 19, 
        ];

        return view('admin.assets.Dashboard', compact('mapConfig', 'assets'));
    }

    /**
     * Tampilan Manajemen Aset (Daftar Tabel)
     * Sinkron dengan Database map_settings
     */
    public function assetIndex()
    {
        $setting = DB::table('map_settings')->first();
        $assets = Asset::all();

        $mapConfig = [
            'center' => [
                (float)($setting->latitude ?? -6.8431), 
                (float)($setting->longitude ?? 107.4912)
            ],
            'defaultZoom' => (int)($setting->zoom ?? 12)
        ];

        return view('admin.assets.index', compact('assets', 'mapConfig'));
    }

    /**
     * Tampilan Form Tambah Aset
     * Sinkron dengan Database map_settings
     */
    public function assetCreate()
    {
        $setting = DB::table('map_settings')->first();
        $mapConfig = [
            'center' => [
                (float)($setting->latitude ?? -6.8431), 
                (float)($setting->longitude ?? 107.4912)
            ],
            'defaultZoom' => (int)($setting->zoom ?? 12)
        ];
        return view('admin.assets.create', compact('mapConfig'));
    }

    /**
     * PROSES SIMPAN: Menyimpan data ke Database
     */
    public function assetStore(Request $request)
    {
        $request->validate([
            'nama'            => 'required|string|max:255',
            'kategori'        => 'required',
            'jenis'           => 'required',
            'status'          => 'required',
            'lat'             => 'required',
            'lng'             => 'required',
            'foto'            => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'merk'            => 'nullable|string|max:100',
            'tgl_pemasangan'  => 'nullable|date',
            'alamat'          => 'nullable|string',
            'catatan'         => 'nullable|string',
        ], [
            'required' => ':attribute wajib diisi!',
            'image'    => 'File harus berupa gambar (JPG/PNG).',
            'mimes'    => 'Format gambar harus jpeg, png, atau jpg.',
            'max'      => 'Ukuran gambar maksimal 2MB.'
        ]);

        try {
            $data = $request->except('foto');

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('assets', $filename, 'public');
                $data['foto'] = $path;
            }

            if (empty($request->id_asset)) {
                $data['id_asset'] = 'AST-' . strtoupper(Str::random(6));
            }

            Asset::create($data);

            return back()->with('success', 'Aset [' . $request->nama . '] berhasil didaftarkan dengan data lengkap!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Edit Aset
     */
    public function assetEdit($id = null)
    {
        $asset = Asset::findOrFail($id);
        return view('admin.assets.edit', compact('asset'));
    }

    /**
     * Pengaturan Peta Global
     */
    public function mapSettings() 
    { 
        $setting = DB::table('map_settings')->first();
        
        $mapConfig = [
            'center' => [
                (float)($setting->latitude ?? -6.8431), 
                (float)($setting->longitude ?? 107.4912)
            ],
            'defaultZoom' => (int)($setting->zoom ?? 11),
            'detailZoom'  => 19,
        ];

        return view('admin.map.map-settings', compact('setting', 'mapConfig'));
    }

    /**
     * Update Koordinat Default Peta
     */
    public function updateMapSettings(Request $request) 
    { 
        $request->validate([
            'latitude'  => 'required',
            'longitude' => 'required',
            'zoom'      => 'required'
        ]);

        try {
            DB::table('map_settings')->updateOrInsert(
                ['id' => 1],
                [
                    'latitude'   => $request->latitude,
                    'longitude'  => $request->longitude,
                    'zoom'       => $request->zoom,
                    'updated_at' => now()
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pengaturan peta berhasil diperbarui!'
                ]);
            }

            return back()->with('success', 'Pengaturan peta berhasil diperbarui!');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function categories() { return view('admin.categories.index'); }
    public function assetTypes() { return view('admin.assets-types.index'); }
}