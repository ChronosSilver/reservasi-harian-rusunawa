<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantAuthController extends Controller
{
    // Tampilkan form login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Cek role, jika admin tolak akses dari portal penyewa
            if (Auth::user()->role === 'admin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Akun Administrator tidak diizinkan masuk melalui portal Penyewa.',
                ])->onlyInput('email');
            }
            
            // Ambil URL intended dari session
            $intendedUrl = redirect()->intended('/')->getTargetUrl();
            $path = parse_url($intendedUrl, PHP_URL_PATH);
            
            // Jika penyewa tapi URL intendednya adalah area admin, buang ke home
            if ($path && str_starts_with($path, '/admin')) {
                return redirect('/');
            }
            
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    // Tampilkan form registrasi
    public function showRegister()
    {
        return view('auth.register');
    }

    // Proses registrasi
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'penyewa', // Default role untuk registrasi publik
            'gender' => 'P', // Default rusunawa putri
        ]);

        Auth::login($user);

        return redirect('/');
    }

    // Proses logout
    public function logout(Request $request)
    {
        // Hanya logout guard 'web' (penyewa)
        Auth::guard('web')->logout();

        // JANGAN gunakan $request->session()->invalidate(); 
        // karena itu akan menghapus seluruh data sesi termasuk milik guard 'admin'.
        // Cukup regenerasi token CSRF untuk keamanan.
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
