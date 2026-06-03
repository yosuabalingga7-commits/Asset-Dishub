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
        // Pastikan relasi 'seksi' dipanggil agar nama Bidang muncul di tabel
        $users = User::with('seksi')->latest()->get();
        
        // Ambil data seksi untuk dropdown modal
        $daftar_seksi = Seksi::all();
        
        return view('admin.users.index', compact('users', 'daftar_seksi'));
    }

    public function store(Request $request)
    {
        // 1. Format nomor WA dulu ke standar 62 sebelum divalidasi
        // Ini penting agar validasi unique di database sinkron
        if ($request->no_wa) {
            $no_wa_formatted = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa_formatted, 0, 1) === '0') {
                $no_wa_formatted = '62' . substr($no_wa_formatted, 1);
            }
            $request->merge(['no_wa' => $no_wa_formatted]);
        }

        // 2. Validasi input dengan pesan kustom bahasa Indonesia
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|numeric|unique:users,nip', 
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'no_wa'    => 'required|unique:users,no_wa', // Cek duplikat WA
            'seksi_id' => ($request->role === 'admin' || $request->role === 'kadis' || $request->role === 'petugas_lapangan') ? 'nullable' : 'required|exists:seksis,id', 
            'role'     => 'required|in:admin,seksi,kadis,petugas_lapangan', // Tambahan validasi role
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'nip.required'      => 'NIP / Username wajib diisi.',
            'nip.numeric'       => 'NIP harus berupa angka.',
            'nip.unique'        => 'Gagal! NIP ini sudah terdaftar di sistem.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Gagal! Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal harus 6 karakter.',
            'no_wa.required'    => 'Nomor WhatsApp wajib diisi.',
            'no_wa.unique'      => 'Gagal! Nomor WhatsApp ini sudah terdaftar. Gunakan nomor lain.',
            'seksi_id.required' => 'Bidang/Seksi wajib dipilih.',
            'role.required'     => 'Role user wajib dipilih.',
        ]);

        try {
            // Eksekusi Pembuatan User
            User::create([
                'name'           => $request->name,
                'nip'            => $request->nip,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'password_plain' => $request->password, // Simpan password asli untuk Admin
                'role'           => $request->role, // Diubah agar dinamis sesuai input (admin/seksi/kadis/petugas_lapangan)
                'no_wa'          => $request->no_wa,
                'seksi_id'       => ($request->role === 'admin' || $request->role === 'kadis' || $request->role === 'petugas_lapangan') ? null : $request->seksi_id,
                'status'         => 'aktif', 
                'is_active'      => true,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Akun berhasil didaftarkan!');

        } catch (\Exception $e) {
            // Jika masih ada error database tak terduga, tampilkan pesan yang lebih rapi
            return back()->withInput()->withErrors(['db_error' => 'Terjadi kesalahan pada sistem. Silakan coba lagi atau cek data yang Anda masukkan.']);
        }
    }

    public function settings($id)
    {
        $user = User::findOrFail($id);
        $daftar_seksi = Seksi::all(); // Tambahkan ini agar di halaman setting bisa ubah seksi
        return view('admin.users.settings', compact('user', 'daftar_seksi'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Format WA sebelum update
        if ($request->no_wa) {
            $no_wa_formatted = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa_formatted, 0, 1) === '0') $no_wa_formatted = '62' . substr($no_wa_formatted, 1);
            $request->merge(['no_wa' => $no_wa_formatted]);
        }

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'no_wa'    => 'nullable|unique:users,no_wa,' . $user->id,
            'seksi_id' => 'nullable|exists:seksis,id',
            'role'     => 'nullable|in:admin,seksi,kadis,petugas_lapangan',
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
            'no_wa.unique' => 'Nomor WhatsApp sudah terdaftar.',
        ]);

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        
        $user->email = $request->email;
        
        if ($request->filled('seksi_id')) {
            $user->seksi_id = $request->seksi_id;
        }
        
        if ($request->filled('no_wa')) {
            $user->no_wa = $request->no_wa;
        }
        
        if ($request->filled('role')) {
            $user->role = $request->role;
        }

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

    // ============================================
    // API METHODS UNTUK DASHBOARD DINAMIS
    // ============================================

    /**
     * API: Mendapatkan total pegawai yang terdaftar di aplikasi
     */
    public function getTotalPegawai()
    {
        $total = User::count();
        return response()->json(['total' => $total]);
    }
}