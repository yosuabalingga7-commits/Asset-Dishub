<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Cek apakah role user ada dalam daftar role yang diizinkan di route
        if (in_array($user->role, $roles)) {
            // Jika akun tidak aktif, tendang keluar
            if (!$user->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan.');
            }
            
            return $next($request);
        }

        // 3. Jika tidak punya akses, arahkan ke dashboard masing-masing dengan pesan error
        return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}