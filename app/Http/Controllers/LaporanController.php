<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;  
use App\Models\Report;
use App\Models\User;
use App\Http\Resources\AssetResource;
use App\Services\ReportService;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    protected $reportService;

    /**
     * Dependency injection of ReportService
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Menampilkan Halaman Depan Utama
     */
    public function indexLanding()
    {
        return view('landing.index');
    }

    /**
     * Menampilkan Form untuk Masyarakat (User Umum)
     */
    public function createPublic()
    {
        $assets = Asset::all();
        return view('masyarakat.lapor', compact('assets'));
    }

    /**
     * Memproses simpan dari form landing page
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
        
        // Eager load 'category' to prevent N+1 query as requested
        $asets = Asset::with('category')->withinRadius((float)$lat, (float)$lng, (int)$radius)->get();
        
        return response()->json([
            'aset' => AssetResource::collection($asets)->resolve()
        ]);
    }

    /**
     * Proses Simpan Laporan dari Masyarakat
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'nama_pelapor' => 'required|string|max:255',
            'kontak_pelapor' => 'required|numeric|digits_between:10,15',
            'judul_laporan' => 'required|string|max:255',
            'kondisi_aset' => 'required', 
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'alamat' => 'required',
            'asset_id' => 'required|exists:assets,id',
        ];
        
        $request->validate($rules);

        try {
            // Pendelegasian logic bisnis ke Service (Service Pattern)
            $report = $this->reportService->createPublicReport(
                $request->all(),
                $request->file('foto')
            );
            
            if (!$report) {
                // Aset sudah dilaporkan atau berstatus kritis/rusak (isAvailableForReport = false)
                $message = 'Terima kasih atas kontribusi Anda! Laporan Anda telah kami terima dan akan segera ditindaklanjuti oleh tim Dishub Kabupaten Bandung Barat.';
                
                if ($request->is('lapor-kerusakan/*') || $request->is('lapor-kerusakan')) {
                    return redirect()->route('landing')->with('success', $message);
                }
                return back()->with('success', $message);
            }
            
            $message = 'Laporan berhasil dikirim! Tiket: ' . $report->ticket_number;
            
            if ($request->is('lapor-kerusakan/*') || $request->is('lapor-kerusakan')) {
                return redirect()->route('landing')->with('success', $message);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Tampilan Daftar Laporan untuk Admin (admin/laporan)
     */
    public function adminIndex()
    {
        $tickets = Report::latest()->get(); 
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Tampilan Manajemen Tiket untuk Admin (admin/tiket)
     */
    public function adminTiket()
    {
        $tickets = Report::latest()->get();
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Detail Laporan/Tiket
     */
    public function show($id)
    {
        $laporan = Report::with('asset')->findOrFail($id);
        
        if(!$laporan->logs) {
            $laporan->logs = collect([]);
        }

        return view('admin.pengaduan.detail', compact('laporan'));
    }

    /**
     * Validasi laporan oleh Admin
     */
    public function validateReport(Request $request, $id)
    {
        $request->validate([
            'kepemilikan' => 'required|in:Dishub,Pihak Ke-3',
            'catatan_admin' => 'nullable|string'
        ]);

        try {
            $laporan = Report::findOrFail($id);
            
            $laporan->update([
                'is_validated' => true,
                'kepemilikan' => $request->kepemilikan,
                'status' => 'Proses Perbaikan',
                'catatan_admin' => $request->catatan_admin
            ]);

            if ($laporan->id_asset) {
                Asset::where('id_asset', $laporan->id_asset)->update([
                    'status' => 'Proses Perbaikan'
                ]);
            }

            return back()->with('success', 'Laporan berhasil divalidasi dengan kepemilikan: ' . $request->kepemilikan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi laporan: ' . $e->getMessage());
        }
    }

    /**
     * Update status oleh Admin
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
            'foto_perbaikan' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'catatan_admin' => 'nullable|string'
        ]);

        try {
            $laporan = Report::findOrFail($id);
            $statusBaru = $request->status;

            $fotoPath = null;
            if ($request->hasFile('foto_perbaikan')) {
                $file = $request->file('foto_perbaikan');
                $fileName = 'perbaikan_' . time() . '_' . $laporan->ticket_number . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('assets/perbaikan', $fileName, 'public');
            }

            $laporan->update([
                'status' => $statusBaru,
                'catatan_admin' => $request->catatan_admin ?? $laporan->catatan_admin
            ]);

            if (strtolower($statusBaru) == 'baik' || strtolower($statusBaru) == 'selesai') {
                if ($laporan->id_asset) {
                    $dataUpdateAset = [
                        'status' => 'Baik',
                        'catatan_terakhir' => $request->catatan_admin
                    ];

                    if ($fotoPath) {
                        $dataUpdateAset['foto_terakhir'] = $fotoPath;
                    }

                    Asset::where('id_asset', $laporan->id_asset)->update($dataUpdateAset);
                }
            }

            return back()->with('success', 'Status laporan dan data aset berhasil diperbarui menjadi: ' . $statusBaru);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status laporan: ' . $e->getMessage());
        }
    }
}