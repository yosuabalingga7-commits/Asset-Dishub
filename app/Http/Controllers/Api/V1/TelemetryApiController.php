<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Report;
use App\Models\User;
use App\Models\MaintenanceTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TelemetryApiController extends Controller
{
    /**
     * GET /api/v1/telemetry/summary
     * Returns high-level KPI metrics for the executive dashboard.
     * Results are cached for 5 minutes (300 seconds) to lower database load.
     */
    public function summary()
    {
        $summary = Cache::remember('telemetry_summary', 300, function () {
            return [
                'total_assets'      => Asset::count(),
                'total_reports'     => Report::count(),
                'active_officers'   => User::where('role', 'petugas_lapangan')->count(),
                'ongoing_tasks'     => MaintenanceTicket::whereIn('status', ['proses', 'pending'])->count(),
                'completed_tasks'   => MaintenanceTicket::where('status', 'selesai')->count(),
            ];
        });

        return response()->json($summary, 200);
    }

    /**
     * GET /api/v1/telemetry/charts
     * Returns aggregated arrays for Category Distribution and Status Condition charts.
     * Results are cached for 5 minutes (300 seconds) to lower database load.
     */
    public function charts()
    {
        $charts = Cache::remember('telemetry_charts', 300, function () {
            // 1. Category Distribution: Group by category column in assets table
            $categoryDist = Asset::select('kategori as name', DB::raw('count(*) as value'))
                ->groupBy('kategori')
                ->get()
                ->toArray();

            // 2. Status Condition: Group by status column in assets table
            $statusDist = Asset::select('status as name', DB::raw('count(*) as value'))
                ->groupBy('status')
                ->get()
                ->toArray();

            return [
                'categories' => $categoryDist,
                'status'     => $statusDist,
            ];
        });

        return response()->json($charts, 200);
    }
}
