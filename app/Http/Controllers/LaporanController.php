<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;  
use App\Models\LaporanMasyarakat; // Ini yang utama kita pakai
use Illuminate\Support\Str;        
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // WAJIB ADA UNTUK KIRIM WA

class LaporanController extends Controller
{
    /**
     * Tampilan Form untuk Masyarakat (User Umum)
     */
    public function index()
    {
        $assets = Asset::all();
        return view('masyarakat.lapor', compact('assets'));
    }

    /**
     * Proses Simpan Laporan dari Masyarakat
     */
    public function store(Request $request)
    {
        // 1. Validasi Input - Update pada kontak_pelapor agar wajib angka (numeric)
        $request->validate([
            'nama_pelapor' => 'required|string|max:255',
            'kontak_pelapor' => 'required|numeric|digits_between:10,15',
            'judul_laporan' => 'required|string|max:255',
            'kondisi_aset' => 'required', 
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
            'lat' => 'required',
            'lng' => 'required',
            'alamat' => 'required', // Tambahkan validasi alamat agar teks lokasi tersimpan
        ]);

        try {
            // 2. Buat ID Tiket Otomatis
            $ticketNumber = 'LP-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 3. Proses Simpan Foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = time() . '_' . $ticketNumber . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('laporan_masyarakat', $fileName, 'public');
            }

            // 4. Simpan ke Database
            LaporanMasyarakat::create([
                'ticket_number' => $ticketNumber,
                'nama_pelapor' => $request->nama_pelapor,
                'kontak_pelapor' => $request->kontak_pelapor,
                'judul_laporan' => $request->judul_laporan,
                'deskripsi_keluhan' => '-', 
                'kondisi_aset' => $request->kondisi_aset,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'alamat' => $request->alamat, // Menyimpan teks alamat dari input peta
                'lokasi_koordinat' => $request->lat . ',' . $request->lng,
                'foto' => $fotoPath,
                'status' => 'masuk',
            ]);

            // --- BAGIAN TAMBAHAN: KIRIM NOTIFIKASI WHATSAPP KE ADMIN ---
            try {
                $nomorAdmin = '088905298517'; // <-- GANTI DENGAN NOMOR WA Dengan No Wa ADMIN
                
                $pesan = "📢 *LAPORAN MASYARAKAT BARU*\n\n"
                       . "🆔 *Tiket:* " . $ticketNumber . "\n"
                       . "👤 *Pelapor:* " . $request->nama_pelapor . "\n"
                       . "📝 *Judul:* " . $request->judul_laporan . "\n"
                       . "📍 *Lokasi:* " . $request->alamat . "\n"
                       . "⚠️ *Kondisi:* " . $request->kondisi_aset . "\n\n"
                       . "Cek detail di Dashboard Admin KBB Smart Asset.";

                // Mengirim perintah ke Node.js di Port 3000
                Http::timeout(5)->post('http://localhost:3000/send-message', [
                    'phone' => $nomorAdmin,
                    'message' => $pesan,
                ]);
            } catch (\Exception $waEx) {
                // Jika WA error, log saja agar aplikasi tidak berhenti (user tetap sukses lapor)
                \Log::error("Gagal Kirim WA: " . $waEx->getMessage());
            }
            // --- AKHIR BAGIAN TAMBAHAN ---

            return back()->with('success', 'Laporan berhasil dikirim! Simpan Nomor Tiket Anda: ' . $ticketNumber);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Daftar Laporan untuk Admin (admin/laporan)
     */
    public function adminIndex()
    {
        // Variabel menggunakan 'tickets' agar sinkron dengan file Blade index
        $tickets = LaporanMasyarakat::latest()->get(); 
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Tampilan Manajemen Tiket untuk Admin (admin/tiket)
     */
    public function adminTiket()
    {
        // Diarahkan ke view yang sama dengan adminIndex
        $tickets = LaporanMasyarakat::latest()->get();
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Detail Laporan/Tiket
     */
    public function show($id)
    {
        // Menggunakan variabel $laporan agar sinkron dengan file Blade detail
        $laporan = LaporanMasyarakat::findOrFail($id);
        
        // Memastikan jika ada logs (relasi), jika belum ada kita kirim koleksi kosong agar tidak error
        if(!$laporan->logs) {
            $laporan->logs = collect([]);
        }

        return view('admin.pengaduan.detail', compact('laporan'));
    }
}