<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;  
use App\Models\LaporanMasyarakat; // Ini yang utama kita pakai
use App\Models\User; // TAMBAHAN UNTUK NOTIFIKASI
use App\Notifications\MaintenanceNotification; // TAMBAHAN UNTUK NOTIFIKASI
use Illuminate\Support\Str;          
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // WAJIB ADA UNTUK KIRIM WA

class LaporanController extends Controller
{
    /**
     * --- TAMBAHAN UNTUK LANDING PAGE LINTAS ---
     * Menampilkan Halaman Depan Utama
     */
    public function indexLanding()
    {
        return view('landing.index');
    }

    /**
     * --- TAMBAHAN UNTUK FORM PUBLIK ---
     * Menampilkan Form untuk Masyarakat (User Umum)
     */
    public function createPublic()
    {
        $assets = Asset::all();
        return view('masyarakat.lapor', compact('assets'));
    }

    /**
     * --- TAMBAHAN UNTUK PROSES SIMPAN PUBLIK ---
     * Memproses simpan dari form landing page (menggunakan method store yang sudah ada)
     */
    public function storePublic(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Tampilan Form untuk Masyarakat (User Umum) - Method Lama
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
            $laporan = LaporanMasyarakat::create([
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

            // --- KIRIM NOTIFIKASI INTERNAL KE SUPER ADMIN ---
            $superAdmins = User::where('role', 'super_admin')->get();
            $notifData = [
                'title' => 'LAPORAN MASYARAKAT BARU',
                'message' => 'Laporan baru dari ' . $request->nama_pelapor . ' mengenai ' . $request->judul_laporan,
                'url' => route('admin.pengaduan.show', $laporan->id),
                'type' => 'info'
            ];
            foreach ($superAdmins as $admin) {
                $admin->notify(new MaintenanceNotification($notifData));
            }

            // --- BAGIAN TAMBAHAN: KIRIM NOTIFIKASI WHATSAPP KE ADMIN ---
            // Dibungkus Try-Catch agar jika server Node.js mati, laporan tetap tersimpan di DB
            try {
                $nomorAdmin = '088905298517'; // <-- NOMOR WA ADMIN KBB
                
                $pesan = "📢 *LAPORAN MASYARAKAT BARU*\n\n"
                       . "🆔 *Tiket:* " . $ticketNumber . "\n"
                       . "👤 *Pelapor:* " . $request->nama_pelapor . "\n"
                       . "📝 *Judul:* " . $request->judul_laporan . "\n"
                       . "📍 *Lokasi:* " . $request->alamat . "\n"
                       . "⚠️ *Kondisi:* " . $request->kondisi_aset . "\n\n"
                       . "Cek detail di Dashboard Admin KBB Smart Asset.";

                // Mengirim perintah ke Node.js di Port 3000
                // Timeout dipersingkat ke 3 detik agar user tidak menunggu lama jika server WA down
                Http::timeout(3)->connectTimeout(3)->post('http://localhost:3000/send-message', [
                    'phone' => $nomorAdmin,
                    'message' => $pesan,
                ]);
            } catch (\Exception $waEx) {
                // Jika WA error, log saja agar aplikasi tidak berhenti (user tetap sukses lapor)
                \Log::error("Gagal Kirim Notifikasi WA (Server Down/Timeout): " . $waEx->getMessage());
            }
            // --- AKHIR BAGIAN TAMBAHAN ---

            // Redirect ke Landing Page dengan pesan sukses jika dari publik
            if ($request->is('lapor-kerusakan/*')) {
                return redirect()->route('landing')->with('success', 'Laporan berhasil dikirim! Tiket: ' . $ticketNumber);
            }

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