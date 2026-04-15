<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login LINTAS.
     */
    public function showLogin()
    {
        // Pastikan file view ini ada di resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Menangani proses autentikasi.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Percobaan Login
        // Menggunakan 'username' sesuai dengan struktur tabel user terbaru
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 3. Cek Status Akun (Aktif/Nonaktif)
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda dinonaktifkan. Silakan hubungi Super Admin.',
                ]);
            }

            // 4. Logika Redirect Berdasarkan Role
            if ($user->role === 'super_admin') {
                return redirect()->intended('/admin/dashboard')
                    ->with('success', 'Selamat Datang Kembali, Super Admin!');
            } 
            
            if ($user->role === 'seksi') {
                return redirect()->intended('/seksi/daftar-tiket')
                    ->with('success', 'Selamat Bekerja, Kepala Seksi.');
            }

            // Default redirect untuk role petugas atau lainnya
            return redirect()->intended('/dashboard');
        }

        // 5. Jika Gagal Login
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    /**
     * Menangani proses logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}