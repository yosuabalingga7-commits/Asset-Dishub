<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket;
use App\Models\Report;
use App\Models\Asset;
use App\Models\User;
use App\Http\Resources\MaintenanceTicketResource;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketApiController extends Controller
{
    /**
     * GET /api/v1/maintenance-tickets
     * Lists all maintenance tickets. Filters by: status, priority, search.
     * Automatically scopes the query to the logged-in user if they are field officers.
     */
    public function index(Request $request)
    {
        $query = MaintenanceTicket::with(['report', 'asset', 'user']);

        // Filter by status (pending, proses, selesai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority (Rendah, Sedang, Tinggi, Darurat)
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search in ticket code, technician name, location address, or asset type
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                  ->orWhere('technician_name', 'like', "%{$search}%")
                  ->orWhere('location_address', 'like', "%{$search}%")
                  ->orWhere('jenis_aset', 'like', "%{$search}%");
            });
        }

        // Role-based scoping: Field officers only see their assigned tickets
        $user = Auth::user();
        if ($user && $user->role === 'petugas_lapangan') {
            $query->where('user_id', $user->id);
        }

        $perPage = $request->get('per_page', 10);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return MaintenanceTicketResource::collection($tickets);
    }

    /**
     * POST /api/v1/maintenance-tickets
     * Creates a new maintenance ticket from a validated report.
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_id' => 'nullable|exists:reports,id',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'jenis_aset' => 'required|string',
            'kepemilikan' => 'required|in:Dishub,Pihak Ke-3',
            'priority' => 'required|in:Rendah,Sedang,Tinggi,Darurat',
            'deadline' => 'required|date',
            'location_address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        try {
            $ticket = DB::transaction(function () use ($request) {
                $userDb = User::findOrFail($request->user_id);
                $laporan = $request->report_id ? Report::find($request->report_id) : null;

                if ($laporan && $laporan->tiket) {
                    throw new \Exception('Tiket untuk laporan ini sudah terdaftar.');
                }

                $ticketCode = 'MNT-' . strtoupper(Str::random(8));
                $finalDescription = $request->description ?? ($laporan ? $laporan->deskripsi_keluhan : 'Perbaikan rutin aset');

                // Resolve asset numeric ID from report or search
                $numericAssetId = $laporan ? $laporan->asset_id : null;

                $newTicket = MaintenanceTicket::create([
                    'ticket_code'      => $ticketCode,
                    'report_id'        => $request->report_id,
                    'asset_id'         => $numericAssetId,
                    'category_id'      => $request->category_id,
                    'category'         => $request->kepemilikan,
                    'kepemilikan'      => $request->kepemilikan,
                    'priority'         => $request->priority,
                    'status'           => 'proses',
                    'location_address' => $request->location_address,
                    'latitude'         => $request->latitude,
                    'longitude'        => $request->longitude,
                    'description'      => $finalDescription,
                    'jenis_aset'       => $request->jenis_aset,
                    'seksi_id'         => $userDb->seksi_id,
                    'user_id'          => $userDb->id,
                    'technician_name'  => $userDb->name,
                    'deadline'         => $request->deadline,
                    'started_at'       => now(),
                ]);

                if ($request->report_id && $laporan) {
                    $laporan->update([
                        'status' => 'Proses Perbaikan',
                        'kepemilikan' => $request->kepemilikan,
                        'updated_at' => now()
                    ]);

                    // Log activity
                    try {
                        $laporan->logs()->create([
                            'aksi' => 'Tiket Dibuat',
                            'keterangan' => "Tiket perbaikan {$ticketCode} telah dibuat dan ditugaskan kepada {$userDb->name}.",
                            'user_id' => Auth::id()
                        ]);
                    } catch (\Exception $logEx) {
                        Log::error('Gagal menulis log audit tiket: ' . $logEx->getMessage());
                    }
                }

                // Notify technician via Database
                try {
                    $userDb->notify(new MaintenanceNotification([
                        'title' => 'PENUGASAN BARU',
                        'message' => 'Tiket perbaikan ' . $ticketCode . ' ditugaskan kepada Anda.',
                        'url' => '/workflow/tasks', // Direct path matching react SPA router
                        'type' => 'urgent'
                    ]));
                } catch (\Exception $notifEx) {
                    Log::error('Gagal mengirim penugasan: ' . $notifEx->getMessage());
                }

                return $newTicket;
            });

            return response()->json([
                'message' => 'Tiket maintenance berhasil dibuat.',
                'data' => new MaintenanceTicketResource($ticket->load(['report', 'asset', 'user']))
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error store ticket API V1: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat tiket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/v1/maintenance-tickets/{id}/complete
     * Submits repair proof (photos & notes) to mark a ticket as completed/solved.
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'foto_perbaikan' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'completion_notes' => 'required|string',
        ]);

        try {
            $ticket = DB::transaction(function () use ($request, $id) {
                $task = MaintenanceTicket::findOrFail($id);

                if ($task->status === 'selesai') {
                    throw new \Exception('Tugas perbaikan ini sudah diselesaikan sebelumnya.');
                }

                $task->status = 'selesai';
                $task->completion_notes = $request->completion_notes;
                $task->finished_at = now();

                if ($request->hasFile('foto_perbaikan')) {
                    $path = $request->file('foto_perbaikan')->store('maintenance/perbaikan', 'public');
                    $task->foto_perbaikan = $path;
                }

                // Restore asset status to good condition
                if ($task->asset_id) {
                    Asset::where('id', $task->asset_id)->update([
                        'status' => 'Baik',
                        'foto_terakhir' => $task->foto_perbaikan,
                        'catatan_terakhir' => $request->completion_notes,
                        'updated_at' => now()
                    ]);
                }

                // Resolve citizen report to completed
                if ($task->report_id) {
                    $laporan = Report::find($task->report_id);
                    if ($laporan) {
                        $laporan->update([
                            'status' => 'Selesai',
                            'catatan_admin' => $request->completion_notes,
                            'updated_at' => now()
                        ]);

                        // Log history audit
                        try {
                            $laporan->logs()->create([
                                'aksi' => 'Perbaikan Selesai',
                                'keterangan' => 'Petugas telah menyelesaikan perbaikan di lapangan. Status aset dikembalikan menjadi Baik.',
                                'user_id' => Auth::id()
                            ]);
                        } catch (\Exception $logEx) {
                            Log::error('Gagal menulis log audit perbaikan selesai: ' . $logEx->getMessage());
                        }
                    }
                }

                $task->save();

                // Alert admins that the ticket is finished
                try {
                    $superAdmins = User::where('role', 'admin')->get();
                    $notifData = [
                        'title' => 'TUGAS SELESAI DIKERJAKAN',
                        'message' => 'Petugas ' . Auth::user()->name . ' telah menyelesaikan tugas: ' . $task->ticket_code,
                        'url' => '/workflow/tickets', // SPA path for admins
                        'type' => 'success'
                    ];
                    
                    foreach ($superAdmins as $admin) {
                        $admin->notify(new MaintenanceNotification($notifData));
                    }
                } catch (\Exception $notifEx) {
                    Log::error('Gagal mengirim alert admin: ' . $notifEx->getMessage());
                }

                return $task;
            });

            return response()->json([
                'message' => 'Laporan perbaikan berhasil disimpan, status aset telah diubah menjadi Baik.',
                'data' => new MaintenanceTicketResource($ticket->load(['report', 'asset', 'user']))
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error complete ticket API V1: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyelesaikan tiket: ' . $e->getMessage()
            ], 500);
        }
    }
}
