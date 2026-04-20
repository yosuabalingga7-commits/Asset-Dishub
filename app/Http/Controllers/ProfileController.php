<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }
        
        $user = auth()->user();
        return view('admin.users.settings', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'no_wa'    => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user->email = $request->email;
        
        if ($request->no_wa) {
            $no_wa = preg_replace('/[^0-9]/', '', $request->no_wa);
            if (substr($no_wa, 0, 1) === '0') {
                $no_wa = '62' . substr($no_wa, 1);
            }
            $user->no_wa = $no_wa;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil Anda berhasil diperbarui!');
    }
}