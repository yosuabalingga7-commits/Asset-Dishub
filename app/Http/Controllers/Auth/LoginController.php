<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip'      => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Logika Redirect Berdasarkan Role
            $user = Auth::user();
            if ($user->role === 'kadis') {
                return redirect()->intended(route('kadis.dashboard'));
            }
            if ($user->role === 'seksi') {
                return redirect()->intended(route('petugas.tersedia'));
            }
            if ($user->role === 'petugas_lapangan') {
                return redirect()->intended(route('petugas.lapangan.dashboard'));
            }
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'nip' => 'NIP atau Password salah.',
        ])->withInput($request->only('nip'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}