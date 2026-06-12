<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Report;
use App\Http\Resources\AssetResource;
use App\Http\Resources\ReportResource;
use App\Services\ReportService;
use Illuminate\Support\Facades\Log;

class ReportApiController extends Controller
{
    protected $reportService;

    /**
     * Dependency injection of ReportService (GRASP Controller / Service Pattern)
     */
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * GET /api/v1/assets/nearest
     * Finds assets within a 100m radius using HasSpatialCoordinates through Asset::withinRadius.
     */
    public function nearestAssets(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $radius = $request->get('radius', 100);

        if (!$lat || !$lng) {
            return response()->json(['aset' => []], 200);
        }

        // Fetch nearest assets eagerly loading categories to avoid N+1 query
        $asets = Asset::with('category')
            ->withinRadius((float) $lat, (float) $lng, (int) $radius)
            ->get();

        return response()->json([
            'aset' => AssetResource::collection($asets)->resolve()
        ], 200);
    }

    /**
     * POST /api/v1/reports/public
     * Submits a public report from a citizen (includes geolocation check).
     */
    public function storePublic(Request $request)
    {
        $request->validate([
            'nama_pelapor' => 'required|string|max:255',
            'kontak_pelapor' => 'required|numeric|digits_between:10,15',
            'judul_laporan' => 'required|string|max:255',
            'kondisi_aset' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'alamat' => 'required|string',
            'asset_id' => 'required|exists:assets,id',
        ]);

        try {
            $report = $this->reportService->createPublicReport(
                $request->all(),
                $request->file('foto')
            );

            if (!$report) {
                // Asset is already reported / unavailable
                return response()->json([
                    'message' => 'Terima kasih atas kontribusi Anda! Laporan Anda telah kami terima dan akan segera ditindaklanjuti oleh tim Dishub Kabupaten Bandung Barat.',
                    'duplicate' => true
                ], 200);
            }

            return response()->json([
                'message' => 'Laporan berhasil dikirim! Tiket: ' . $report->ticket_number,
                'data' => new ReportResource($report)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error Simpan Pengaduan API V1: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyimpan laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/v1/reports/officer
     * Submits a professional maintenance request or observation from an field officer.
     */
    public function storeOfficer(Request $request)
    {
        $request->validate([
            'nama_pelapor'   => 'required|string|max:255',
            'nip'            => 'required|string',
            'kontak_pelapor' => 'required|string',
            'judul_laporan'  => 'required|string',
            'kondisi_aset'   => 'required|string',
            'deskripsi'      => 'nullable|string',
            'foto'           => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'lat'            => 'required|numeric',
            'lng'            => 'required|numeric',
            'asset_id'       => 'required|exists:assets,id',
        ]);

        try {
            $report = $this->reportService->createOfficerReport(
                $request->all(),
                $request->file('foto')
            );

            return response()->json([
                'message' => 'Laporan Teknis Berhasil Disimpan! Kode Tiket: ' . $report->ticket_number,
                'data' => new ReportResource($report)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error Simpan Laporan Petugas API V1: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyimpan laporan petugas: ' . $e->getMessage()
            ], 500);
        }
    }
}
