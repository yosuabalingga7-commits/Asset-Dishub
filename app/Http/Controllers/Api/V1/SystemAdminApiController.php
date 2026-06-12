<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seksi;
use App\Models\Report;
use App\Models\MaintenanceLog;
use App\Models\ReportLog;
use App\Models\MapSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SystemAdminApiController extends Controller
{
    /**
     * GET /api/v1/users
     * Returns a paginated list of users, along with lists of seksi divisions.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::with('seksi')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15);
        $seksis = Seksi::all();

        return response()->json([
            'users' => $users,
            'seksis' => $seksis
        ], 200);
    }

    /**
     * POST /api/v1/users
     * Registers a new user.
     */
    public function store(Request $request)
    {
        // Format WhatsApp number
        if ($request->no_wa) {
            $no_wa_formatted = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa_formatted, 0, 1) === '0') {
                $no_wa_formatted = '62' . substr($no_wa_formatted, 1);
            }
            $request->merge(['no_wa' => $no_wa_formatted]);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|numeric|unique:users,nip',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'no_wa'    => 'required|unique:users,no_wa',
            'role'     => 'required|in:admin,seksi,kadis,petugas_lapangan',
            'seksi_id' => $request->role === 'seksi' ? 'required|exists:seksis,id' : 'nullable|exists:seksis,id',
        ]);

        $user = User::create([
            'name'           => $request->name,
            'nip'            => $request->nip,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'password_plain' => $request->password, // Plain password stored for admin convenience
            'role'           => $request->role,
            'no_wa'          => $request->no_wa,
            'seksi_id'       => in_array($request->role, ['admin', 'kadis', 'petugas_lapangan']) ? null : $request->seksi_id,
            'status'         => 'aktif',
            'is_active'      => true,
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'user' => $user->load('seksi')
        ], 201);
    }

    /**
     * PUT /api/v1/users/{id}
     * Updates an existing user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Format WhatsApp number
        if ($request->no_wa) {
            $no_wa_formatted = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa_formatted, 0, 1) === '0') {
                $no_wa_formatted = '62' . substr($no_wa_formatted, 1);
            }
            $request->merge(['no_wa' => $no_wa_formatted]);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'no_wa'    => 'required|unique:users,no_wa,' . $user->id,
            'role'     => 'required|in:admin,seksi,kadis,petugas_lapangan',
            'seksi_id' => $request->role === 'seksi' ? 'required|exists:seksis,id' : 'nullable|exists:seksis,id',
            'password' => 'nullable|min:6',
            'is_active'=> 'nullable|boolean',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_wa = $request->no_wa;
        $user->role = $request->role;
        $user->seksi_id = in_array($request->role, ['admin', 'kadis', 'petugas_lapangan']) ? null : $request->seksi_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_plain = $request->password;
        }

        if ($request->has('is_active')) {
            $user->is_active = (bool) $request->is_active;
            $user->status = $request->is_active ? 'aktif' : 'nonaktif';
        }

        $user->save();

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'user' => $user->load('seksi')
        ], 200);
    }

    /**
     * DELETE /api/v1/users/{id}
     * Deletes a user account. Blocks self-deletion.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return response()->json([
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri!'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.'
        ], 200);
    }

    /**
     * GET /api/v1/audit-logs
     * Returns searchable, paginated consolidated activity logs.
     */
    public function auditLogs(Request $request)
    {
        $search = $request->input('search');
        $logs = collect();

        // 1. Citizen report creations
        $citizenQuery = Report::where('source', 'masyarakat');
        if ($search) {
            $citizenQuery->where(function ($q) use ($search) {
                $q->where('nama_pelapor', 'like', "%{$search}%")
                  ->orWhere('judul_laporan', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        $citizenReports = $citizenQuery->orderBy('created_at', 'desc')->take(100)->get();
        foreach ($citizenReports as $item) {
            $logs->push([
                'id' => 'rep-m-' . $item->id,
                'user' => $item->nama_pelapor,
                'role' => 'Masyarakat',
                'type' => 'CREATE',
                'modul' => 'LAPORAN MASYARAKAT',
                'action' => 'Membuat Laporan Baru',
                'target' => '#' . ($item->ticket_number ?? 'LP-' . $item->id),
                'desc' => 'Melaporkan kerusakan: ' . $item->judul_laporan,
                'time' => $item->created_at->format('Y-m-d H:i:s'),
                'created_at' => $item->created_at
            ]);
        }

        // 2. Officer report creations
        $officerQuery = Report::where('source', 'petugas');
        if ($search) {
            $officerQuery->where(function ($q) use ($search) {
                $q->where('nama_petugas', 'like', "%{$search}%")
                  ->orWhere('judul_laporan', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        $officerReports = $officerQuery->orderBy('created_at', 'desc')->take(100)->get();
        foreach ($officerReports as $item) {
            $logs->push([
                'id' => 'rep-p-' . $item->id,
                'user' => $item->nama_petugas,
                'role' => 'Petugas Lapangan',
                'type' => 'CREATE',
                'modul' => 'LAPORAN PETUGAS',
                'action' => 'Membuat Laporan Teknis',
                'target' => '#' . ($item->ticket_number ?? 'LP-P-' . $item->id),
                'desc' => 'Melaporkan temuan: ' . $item->judul_laporan,
                'time' => $item->created_at->format('Y-m-d H:i:s'),
                'created_at' => $item->created_at
            ]);
        }

        // 3. Maintenance log changes
        $maintLogsQuery = MaintenanceLog::with(['user', 'ticket']);
        if ($search) {
            $maintLogsQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('ticket', function ($q) use ($search) {
                    $q->where('ticket_code', 'like', "%{$search}%");
                })->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }
        $maintLogs = $maintLogsQuery->orderBy('created_at', 'desc')->take(100)->get();
        foreach ($maintLogs as $item) {
            $logs->push([
                'id' => 'mnt-log-' . $item->id,
                'user' => $item->user->name ?? 'Sistem',
                'role' => ucfirst($item->user->role ?? 'Petugas'),
                'type' => 'UPDATE',
                'modul' => 'MAINTENANCE',
                'action' => $item->action ?? 'Memperbarui Tiket',
                'target' => '#' . ($item->ticket->ticket_code ?? 'MTC-' . $item->ticket_id),
                'desc' => $item->note ?? 'Pembaruan status tiket',
                'time' => $item->created_at->format('Y-m-d H:i:s'),
                'created_at' => $item->created_at
            ]);
        }

        // 4. Report validations
        $repLogsQuery = ReportLog::with(['user', 'report']);
        if ($search) {
            $repLogsQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('report', function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%");
                })->orWhere('aksi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }
        $repLogs = $repLogsQuery->orderBy('created_at', 'desc')->take(100)->get();
        foreach ($repLogs as $item) {
            $logs->push([
                'id' => 'rep-log-' . $item->id,
                'user' => $item->user->name ?? 'Sistem',
                'role' => ucfirst($item->user->role ?? 'Seksi'),
                'type' => 'VALIDASI',
                'modul' => 'TRIAGE LAPORAN',
                'action' => $item->aksi ?? 'Validasi Laporan',
                'target' => '#' . ($item->report->ticket_number ?? 'LP-' . $item->report_id),
                'desc' => $item->keterangan ?? 'Memproses aduan masyarakat',
                'time' => $item->created_at->format('Y-m-d H:i:s'),
                'created_at' => $item->created_at
            ]);
        }

        // 5. User accounts logs
        $userQuery = User::query();
        if ($search) {
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }
        $users = $userQuery->orderBy('updated_at', 'desc')->take(100)->get();
        foreach ($users as $item) {
            $logs->push([
                'id' => 'usr-log-' . $item->id . '-' . $item->updated_at->timestamp,
                'user' => $item->name,
                'role' => ucfirst($item->role ?? 'User'),
                'type' => $item->created_at == $item->updated_at ? 'CREATE' : 'UPDATE',
                'modul' => 'AKUN PENGGUNA',
                'action' => $item->created_at == $item->updated_at ? 'Registrasi Akun' : 'Pembaruan Akun',
                'target' => $item->nip ?? 'N/A',
                'desc' => 'Status: ' . ($item->is_active ? 'Aktif' : 'Nonaktif'),
                'time' => $item->updated_at->format('Y-m-d H:i:s'),
                'created_at' => $item->updated_at
            ]);
        }

        // Sort chronologically and paginate
        $sortedLogs = $logs->sortByDesc('created_at')->values();

        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $total = $sortedLogs->count();
        $paginated = $sortedLogs->forPage($page, $perPage)->values();

        return response()->json([
            'data' => $paginated,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage)
        ], 200);
    }

    /**
     * GET /api/v1/system/map-settings
     * Fetches default Map center and zoom level.
     */
    public function getMapSettings()
    {
        $setting = MapSetting::firstOrCreate([], [
            'latitude' => -6.8431,
            'longitude' => 107.4912,
            'zoom' => 11
        ]);

        return response()->json($setting, 200);
    }

    /**
     * PUT /api/v1/system/map-settings
     * Updates default Map center and zoom level.
     */
    public function updateMapSettings(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'zoom'      => 'required|integer|min:0|max:20',
        ]);

        $setting = MapSetting::first();
        if (!$setting) {
            $setting = new MapSetting();
        }

        $setting->latitude = $request->latitude;
        $setting->longitude = $request->longitude;
        $setting->zoom = $request->zoom;
        $setting->save();

        return response()->json([
            'message' => 'Pengaturan peta berhasil disimpan.',
            'setting' => $setting
        ], 200);
    }
}
