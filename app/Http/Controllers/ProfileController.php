<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

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

    // Fungsi Update Foto dengan Dukungan Cropping (Base64)
    public function updateFoto(Request $request)
    {
        $user = Auth::user();

        // Cek jika ada kiriman data cropped image
        if ($request->filled('cropped_image')) {
            $imageData = $request->cropped_image;
            
            // Decode Base64 string
            $image_parts = explode(";base64,", $imageData);
            $image_base64 = base64_decode($image_parts[1]);
            
            // Nama file unik
            $fileName = 'profile-photos/' . uniqid() . '.jpg';

            // Simpan ke storage (public disk)
            Storage::disk('public')->put($fileName, $image_base64);

            // Hapus foto lama jika ada
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Update path di DB
            $user->profile_photo_path = $fileName;
            $user->save();

            return back()->with('success', 'Foto profil berhasil diperbarui!');
        }

        // Fallback untuk upload biasa jika dibutuhkan
        if ($request->hasFile('profile_photo')) {
            $request->validate(['profile_photo' => 'image|max:2048']);
            if ($user->profile_photo_path) { Storage::delete($user->profile_photo_path); }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
            $user->save();
            return back()->with('success', 'Foto profil berhasil diperbarui!');
        }

        return back()->with('error', 'Gagal memproses foto.');
    }
}