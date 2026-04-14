<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Penting: Import Model User
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman pengaturan akun.
     */
    public function index()
    {
        // Ambil user pertama di database
        $user = User::first();

        /**
         * PERBAIKAN: Jika database kosong, kita buatkan data PERMANEN di DB.
         * Ini supaya user punya ID dan bisa di-update.
         */
        if (!$user) {
            $user = User::create([
                'name'      => 'Naufal Paman',
                'email'     => 'naufal@dishub.go.id',
                'password'  => Hash::make('password123'),
                'nip'       => '199208152023011001',
                'role'      => 'petugas',
                'is_active' => true,
            ]);
        }

        return view('admin.users.settings', compact('user'));
    }

    /**
     * Memperbarui data profil petugas.
     */
    public function update(Request $request)
    {
        // Selalu sasar user pertama
        $user = User::first();

        if (!$user) {
            return back()->with('error', 'Data user tidak ditemukan di database, Ketua!');
        }

        // 1. Validasi Input
        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'no_wa'    => 'nullable|string|max:15',
            'foto'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'foto.max' => 'Ukuran foto maksimal adalah 2MB.',
        ]);

        // 2. Logika Update Foto Profil
        if ($request->hasFile('foto')) {
            // Hapus foto lama dari storage jika ada
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            // Simpan foto baru ke folder 'public/profiles'
            $path = $request->file('foto')->store('profiles', 'public');
            $user->foto = $path;
        }

        // 3. Update Informasi Dasar
        $user->email = $request->email;
        $user->no_wa = $request->no_wa;

        // 4. Update Password (Hanya jika diisi)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 5. Simpan Perubahan ke Database
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Profil Anda berhasil diperbarui!');
    }
}