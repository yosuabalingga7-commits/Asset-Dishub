<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanPetugas;
use App\Models\Asset;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LaporanPetugasController extends Controller
{
    /**
     * Menampilkan halaman form laporan teknis petugas
     * Data nama, nip, no_wa otomatis terisi dari user yang login
     */
    public function create()
    {
        return view('admin.laporan.laporan_petugas');
    }

    /**
     * API untuk mencari semua aset terdekat dalam radius
     */
    public function nearestAssets(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $radius = $request->get('radius', 100);
        
        if (!$lat || !$lng) {
            return response()->json(['aset' => []]);
        }
        
        $asets = Asset::withinRadius((float)$lat, (float)$lng, (int)$radius)->get();
        
        return response()->json([
            'aset' => $asets->map(function($item) {
                return [
                    'id' => $item->id,
                    'id_asset' => $item->id_asset,
                    'nama' => $item->nama,
                    'kategori' => $item->kategori,
                    'lat' => (float)$item->lat,
                    'lng' => (float)$item->lng,
                    'jarak' => (float)$item->distance
                ];
            })
        ]);
    }

    /**
     * Menyimpan data laporan petugas ke database
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'nama_pelapor'   => 'required|string|max:255',
            'nip'            => 'required|string',
            'kontak_pelapor' => 'required',
            'judul_laporan'  => 'required',
            'kondisi_aset'   => 'required',
            'deskripsi'      => 'nullable|string',
            'foto'           => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat'            => 'required',
            'lng'            => 'required',
            'asset_id'       => 'required|exists:assets,id',
        ];
        
        $request->validate($rules);

        try {
            // 2. Ambil aset yang dipilih
            $asset = Asset::find($request->asset_id);
            if (!$asset) {
                throw new \Exception('Aset tidak ditemukan');
            }

            // 3. Buat ID Tiket Otomatis
            $ticketNumber = 'LP-P-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 4. Proses Upload Foto
            $nama_foto = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $nama_foto = time() . '_petugas_' . $ticketNumber . '_' . $file->getClientOriginalName();
                
                $tujuan_upload = public_path('uploads/laporan_petugas');
                if (!File::isDirectory($tujuan_upload)) {
                    File::makeDirectory($tujuan_upload, 0777, true, true);
                }
                $file->move($tujuan_upload, $nama_foto);
            }

            // 5. Simpan ke Database
            $laporan = LaporanPetugas::create([
                'ticket_number' => $ticketNumber,
                'nama_petugas'  => $request->nama_pelapor,
                'nip'           => $request->nip,
                'no_wa'         => $request->kontak_pelapor,
                'judul_laporan' => $request->judul_laporan,
                'kondisi_aset'  => $request->kondisi_aset,
                'deskripsi'     => $request->deskripsi,
                'foto'          => $nama_foto,
                'lat'           => $request->lat,
                'lng'           => $request->lng,
                'status'        => 'masuk',
                'asset_id'      => $asset->id,
                'id_asset'      => $asset->id_asset,
                'sumber_laporan' => 'petugas',
            ]);

            // 6. UPDATE STATUS ASET
            $kondisi = $request->input('kondisi_aset');
            
            if (in_array($kondisi, ['Hilang/Dicuri', 'Lainnya'])) {
                $statusBaru = 'Kritis';
            } else {
                $statusBaru = 'Rusak';
            }
            
            $asset->update(['status' => $statusBaru]);
            
            \Log::info('Status aset diperbarui via LaporanPetugasController', [
                'id_asset' => $asset->id_asset,
                'kondisi_laporan' => $kondisi,
                'status_baru' => $statusBaru,
                'ticket_number' => $ticketNumber
            ]);

            return redirect()->back()->with('success', 'Laporan Teknis Berhasil Disimpan! Kode Tiket: ' . $ticketNumber);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar laporan petugas (untuk admin)
     */
    public function index()
    {
        $laporanPetugas = LaporanPetugas::latest()->paginate(10);
        return view('admin.pengaduan.petugas', compact('laporanPetugas'));
    }

    /**
     * Menampilkan detail laporan petugas
     */
    public function show($id)
    {
        $laporan = LaporanPetugas::findOrFail($id);
        return view('admin.laporan.detail', compact('laporan'));
    }
}