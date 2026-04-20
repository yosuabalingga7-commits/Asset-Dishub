<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        // Menampilkan semua user (latest() agar yang baru dibuat ada di atas)
        $users = User::with('seksi')->latest()->get();
        
        // Ambil data seksi untuk dropdown modal
        $daftar_seksi = Seksi::all();
        
        return view('admin.users.index', compact('users', 'daftar_seksi'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|numeric|unique:users,nip', 
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'no_wa'    => 'required|string',
            'seksi_id' => 'required', // Jika error 'exists', coba pastikan tabel seksis ada isinya
        ]);

        try {
            // Format nomor WA ke standar 62
            $no_wa = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa, 0, 1) === '0') {
                $no_wa = '62' . substr($no_wa, 1);
            }

            // Eksekusi Pembuatan User
            User::create([
                'name'           => $request->name,
                'nip'            => $request->nip,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'password_plain' => $request->password, // Simpan password asli untuk Admin
                'role'           => 'seksi', 
                'no_wa'          => $no_wa,
                'seksi_id'       => $request->seksi_id,
                'status'         => 'aktif', 
                'is_active'      => true,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Akun Seksi berhasil didaftarkan!');

        } catch (\Exception $e) {
            // Jika ada error database, balikkan dengan pesan error dan buka modal lagi
            return back()->withInput()->withErrors(['db_error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    public function settings($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.settings', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'no_wa' => 'nullable|string',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->no_wa) {
            $no_wa = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa, 0, 1) === '0') $no_wa = '62' . substr($no_wa, 1);
            $user->no_wa = $no_wa;
        }

        // Jika password diisi saat edit, update juga password_plain-nya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_plain = $request->password; 
        }

        $user->save();
        return back()->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->id() == $user->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }
        
        $user->delete();
        return back()->with('success', 'Akun berhasil dihapus!');
    }
}