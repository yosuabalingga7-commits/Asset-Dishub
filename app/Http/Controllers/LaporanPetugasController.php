<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Asset;
use App\Http\Resources\AssetResource;
use App\Services\ReportService;

class LaporanPetugasController extends Controller
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
     * Menampilkan halaman form laporan teknis petugas
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
        
        // Eager load 'category' to prevent N+1 query
        $asets = Asset::with('category')->withinRadius((float)$lat, (float)$lng, (int)$radius)->get();
        
        return response()->json([
            'aset' => AssetResource::collection($asets)->resolve()
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
            // Mendelegasikan logic bisnis ke Service
            $report = $this->reportService->createOfficerReport(
                $request->all(),
                $request->file('foto')
            );

            return redirect()->back()->with('success', 'Laporan Teknis Berhasil Disimpan! Kode Tiket: ' . $report->ticket_number);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan daftar laporan petugas (untuk admin)
     */
    public function index()
    {
        $laporanPetugas = Report::where('source', 'petugas')->latest()->paginate(10);
        return view('admin.pengaduan.petugas', compact('laporanPetugas'));
    }

    /**
     * Menampilkan detail laporan petugas
     */
    public function show($id)
    {
        $laporan = Report::where('source', 'petugas')->findOrFail($id);
        return view('admin.laporan.detail', compact('laporan'));
    }
}