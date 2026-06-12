<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Asset;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WorkflowApiController extends Controller
{
    /**
     * GET /api/v1/reports
     * Retrieves a paginated list of reports, with optional source, status, and search filters.
     */
    public function index(Request $request)
    {
        // Eager load asset and logs to prevent N+1 queries
        $query = Report::with(['asset', 'logs']);

        // Filter by source (masyarakat, petugas)
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by status (masuk, Proses Perbaikan, Selesai, Ditolak)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search in title, reporter name, ticket number, or address
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_laporan', 'like', "%{$search}%")
                  ->orWhere('nama_pelapor', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $reports = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ReportResource::collection($reports);
    }

    /**
     * PATCH /api/v1/reports/{id}/validate
     * Validates a report's ownership and shifts status to 'Proses Perbaikan'
     */
    public function validateReport(Request $request, $id)
    {
        $request->validate([
            'kepemilikan' => 'required|in:Dishub,Pihak Ke-3',
            'catatan_admin' => 'nullable|string'
        ]);

        try {
            $report = Report::findOrFail($id);

            DB::transaction(function () use ($request, $report) {
                $report->update([
                    'is_validated' => true,
                    'kepemilikan' => $request->kepemilikan,
                    'status' => 'Proses Perbaikan',
                    'catatan_admin' => $request->catatan_admin ?? 'Validasi otomatis.'
                ]);

                if ($report->asset_id) {
                    Asset::where('id', $report->asset_id)->update([
                        'status' => 'Proses Perbaikan'
                    ]);
                }

                // Log the action to report_logs
                try {
                    $report->logs()->create([
                        'aksi' => 'Laporan Divalidasi',
                        'keterangan' => "Laporan divalidasi sebagai aset: " . strtoupper($request->kepemilikan) . ". Status aset diubah menjadi Proses Perbaikan. Catatan: " . $request->catatan_admin,
                        'user_id' => Auth::id()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal membuat log audit validasi: ' . $e->getMessage());
                }
            });

            return response()->json([
                'message' => 'Laporan divalidasi sebagai ' . strtoupper($request->kepemilikan) . '. Status aset telah diubah menjadi Proses Perbaikan.',
                'data' => new ReportResource($report->load(['asset', 'logs']))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error validasi laporan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memvalidasi laporan: ' . $e->getMessage()
            ], 500);
        }
    }
}
