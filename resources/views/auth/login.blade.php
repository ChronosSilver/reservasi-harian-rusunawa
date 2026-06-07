@extends('layouts.app')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Selamat Datang Kembali</h2>
        <p>Silakan masuk ke akun penyewa Anda</p>
        
        <form method="POST" action="{{ url('/login') }}" class="auth-form">
            @csrf
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="contoh: budi@student.untan.ac.id" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Masukkan kata sandi Anda" required>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <p class="auth-switch">Belum punya akun? <a href="{{ url('/register') }}">Daftar di sini</a></p>
    </div>
</div>
@endsection
