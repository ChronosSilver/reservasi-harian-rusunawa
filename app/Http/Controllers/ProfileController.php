<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman detail profil penyewa.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return view('tenant.profile', compact('user'));
    }

    /**
     * Memperbarui detail profil penyewa.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'gender' => 'required|in:L,P',
            'identity_type' => 'required|in:NIM,NIP,NIK',
            'identity_number' => 'required|string|max:50|unique:users,identity_number,' . $user->id,
            'phone_number' => 'required|string|max:15',
        ], [
            'identity_number.unique' => 'Nomor identitas ini sudah digunakan oleh akun lain.',
            'phone_number.max' => 'Nomor telepon tidak boleh lebih dari 15 karakter.'
        ]);

        $user->update([
            'gender' => $request->gender,
            'identity_type' => $request->identity_type,
            'identity_number' => $request->identity_number,
            'phone_number' => $request->phone_number,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Memperbarui kata sandi penyewa.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini tidak cocok.'])->with('password_modal', true);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui!');
    }
}
