<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanPetugas;
use Illuminate\Support\Facades\File;

class LaporanPetugasController extends Controller
{
    /**
     * Menampilkan halaman form laporan teknis petugas
     */
    public function create()
    {
        return view('admin.laporan.laporan_petugas');
    }

    /**
     * Menyimpan data laporan petugas ke database
     */
    public function store(Request $request)
    {
        // 1. Validasi Input (Menambahkan deskripsi)
        $request->validate([
            'nama_pelapor'   => 'required|string|max:255',
            'nip'            => 'required|string',
            'kontak_pelapor' => 'required',
            'judul_laporan'  => 'required',
            'kondisi_aset'   => 'required',
            'deskripsi'      => 'nullable|string', // Kolom deskripsi baru
            'foto'           => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat'            => 'required',
            'lng'            => 'required',
        ]);

        try {
            // 2. Proses Upload Foto ke public/uploads/laporan_petugas
            $nama_foto = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $nama_foto = time() . '_petugas_' . $file->getClientOriginalName();
                
                // Pastikan direktori ada, jika tidak buat otomatis
                $tujuan_upload = public_path('uploads/laporan_petugas');
                if (!File::isDirectory($tujuan_upload)) {
                    File::makeDirectory($tujuan_upload, 0777, true, true);
                }

                $file->move($tujuan_upload, $nama_foto);
            }

            // 3. Simpan ke Database (Mapping input ke kolom model)
            LaporanPetugas::create([
                'nama_petugas'  => $request->nama_pelapor,   // Map dari input ke kolom DB
                'nip'           => $request->nip,
                'no_wa'         => $request->kontak_pelapor, // Map dari input ke kolom DB
                'judul_laporan' => $request->judul_laporan,
                'kondisi_aset'  => $request->kondisi_aset,
                'deskripsi'     => $request->deskripsi,      // Kolom deskripsi baru disimpan
                'foto'          => $nama_foto,
                'lat'           => $request->lat,
                'lng'           => $request->lng,
                'status'        => 'masuk',
            ]);

            return redirect()->back()->with('success', 'Laporan Teknis Berhasil Disimpan!');

        } catch (\Exception $e) {
            // Log error jika diperlukan untuk debugging
            return redirect()->back()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }
}