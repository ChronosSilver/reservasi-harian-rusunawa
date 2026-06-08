@extends('layouts.app')

@section('content')
<div class="container profile-container min-h-80vh">
    
    <div id="profilePageData" class="d-none"
        data-require-identity="{{ session('require_identity') ? 'true' : 'false' }}"
        data-password-error="{{ session('password_modal') || $errors->has('current_password') || $errors->has('new_password') ? 'true' : 'false' }}">
    </div>

    <div class="dashboard-header flex-header profile-header">
        <div>
            <h1>Pengaturan Profil</h1>
            <p class="mb-0">Kelola informasi pribadi dan kontak Anda di sistem Rusunawa Untan.</p>
        </div>
    </div>

    <div class="dashboard-panel profile-panel">
        
        <div class="profile-info-bar">
            @php
                $names = explode(' ', $user->name);
                $initials = '';
                foreach (array_slice($names, 0, 2) as $namePart) {
                    $initials .= strtoupper(substr($namePart, 0, 1));
                }
            @endphp
            <div class="profile-avatar-large">
                {{ $initials }}
            </div>
            <div>
                <h2 class="profile-name-large">{{ $user->name }}</h2>
                <div class="profile-meta-row">
                    <p class="profile-joined-text">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Bergabung sejak {{ $user->created_at->format('d M Y') }}
                    </p>
                    <span class="badge profile-badge-tenant">
                        <svg class="svg-icon-inline" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ $user->role === 'penyewa' ? 'Penyewa' : 'Administrator' }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" class="profile-form-body">
            @csrf
            
            @if(session('success'))
                <div class="profile-alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="profile-alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h3 class="profile-section-title">Informasi Dasar</h3>

            <div class="profile-grid-1-1">
                <div>
                    <label class="profile-label">Nama Lengkap</label>
                    <input type="text" value="{{ $user->name }}" readonly class="profile-input-readonly">
                    <small class="profile-input-hint">Nama terikat dengan akun pendaftaran.</small>
                </div>
                <div>
                    <label class="profile-label">Alamat Email</label>
                    <input type="email" value="{{ $user->email }}" readonly class="profile-input-readonly">
                    <small class="profile-input-hint">Email terikat dengan akun pendaftaran.</small>
                </div>
            </div>

            <h3 class="profile-section-title">Informasi Personal & Kontak</h3>

            <div class="profile-grid-1-1">
                <div>
                    <label for="gender" class="profile-label">Jenis Kelamin <span class="profile-label-required">*</span></label>
                    <div class="profile-select-wrapper">
                        <select name="gender" id="gender" required class="profile-select">
                            <option value="P" {{ old('gender', $user->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            <option value="L" {{ old('gender', $user->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        </select>
                        <svg class="profile-select-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
                
                <div>
                    <label for="phone_number" class="profile-label">Nomor Telepon <span class="profile-label-required">*</span></label>
                    <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required placeholder="Contoh: 08123456789" class="profile-input">
                </div>
            </div>

            <div class="profile-grid-1-2">
                <div>
                    <label for="identity_type" class="profile-label">Tipe Identitas <span class="profile-label-required">*</span></label>
                    <div class="profile-select-wrapper">
                        <select name="identity_type" id="identity_type" required class="profile-select">
                            <option value="NIM" {{ old('identity_type', $user->identity_type) === 'NIM' ? 'selected' : '' }}>NIM</option>
                            <option value="NIP" {{ old('identity_type', $user->identity_type) === 'NIP' ? 'selected' : '' }}>NIP</option>
                            <option value="NIK" {{ old('identity_type', $user->identity_type) === 'NIK' ? 'selected' : '' }}>NIK</option>
                        </select>
                        <svg class="profile-select-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
                <div>
                    <label for="identity_number" class="profile-label">Nomor Identitas <span class="profile-label-required">*</span></label>
                    <input type="text" name="identity_number" id="identity_number" value="{{ old('identity_number', $user->identity_number) }}" required placeholder="Masukkan nomor identitas" class="profile-input">
                </div>
            </div>

            <div class="profile-form-footer">
                <div class="profile-password-hint" style="display:flex; align-items:center; gap: 15px;">
                    <span>Kata sandi Anda dienkripsi secara aman (<span class="profile-password-stars">********</span>)</span>
                    <button type="button" class="btn btn-outline" style="padding: 6px 15px; font-size: 0.85rem;" onclick="openPasswordModal()">Ganti Sandi</button>
                </div>
                <button type="submit" class="btn btn-primary btn-icon">
                    <svg class="svg-icon-btn" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<!-- Modal Peringatan Identitas -->
<div id="identityModal" class="modal-overlay">
    <div class="modal-content identity-modal">
        <button type="button" class="modal-close" style="position: absolute; right: 15px; top: 15px; z-index: 10;" onclick="closeIdentityModal()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div class="identity-modal-body">
            <div class="identity-icon-wrapper">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h4 class="identity-modal-title">Identitas Belum Lengkap</h4>
            <p class="identity-modal-text">Anda harus melengkapi profil (Tipe & Nomor Identitas) sebelum dapat membuat reservasi kamar.</p>
        </div>
        <div class="identity-modal-footer">
            <button type="button" class="identity-btn text-white" onclick="closeIdentityModal()">Lengkapi Sekarang</button>
        </div>
    </div>
</div>

<!-- Modal Ganti Kata Sandi -->
<div id="passwordModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ganti Kata Sandi</h3>
            <button type="button" class="close-btn" onclick="closePasswordModal()">&times;</button>
        </div>
        <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            <div class="modal-body">
                @if(session('password_modal') && $errors->any())
                    <div class="profile-alert-danger" style="margin-bottom: 20px;">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div style="margin-bottom: 15px;">
                    <label class="profile-label">Kata Sandi Saat Ini</label>
                    <div class="profile-password-wrapper">
                        <input type="password" name="current_password" id="current_pwd" required class="profile-input">
                        <button type="button" class="profile-password-toggle" onclick="togglePassword('current_pwd', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label class="profile-label">Kata Sandi Baru</label>
                    <div class="profile-password-wrapper">
                        <input type="password" name="new_password" id="new_pwd" required class="profile-input" minlength="8">
                        <button type="button" class="profile-password-toggle" onclick="togglePassword('new_pwd', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div style="margin-bottom: 5px;">
                    <label class="profile-label">Konfirmasi Kata Sandi Baru</label>
                    <div class="profile-password-wrapper">
                        <input type="password" name="new_password_confirmation" id="new_pwd_conf" required class="profile-input" minlength="8">
                        <button type="button" class="profile-password-toggle" onclick="togglePassword('new_pwd_conf', this)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-px-20" onclick="closePasswordModal()">Batal</button>
                <button type="submit" class="btn btn-primary btn-px-20">Simpan Sandi Baru</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/profile.js') }}?v={{ filemtime(public_path('js/profile.js')) }}"></script>
@endpush
@endsection
