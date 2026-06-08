@extends('layouts.app')

@section('content')
<div class="container form-container min-h-80vh p-form">
    
    <!-- Tombol Kembali -->
    <a href="{{ route('reservations.index') }}" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke Reservasi
    </a>

    <div class="form-wrapper">
        <div class="form-header">
            <h2 class="h2-title">Pembayaran Reservasi</h2>
            <p class="subtitle-text">Silakan lakukan pembayaran sesuai tagihan ke salah satu rekening di bawah ini.</p>
        </div>

        <div class="form-body">
            @if ($errors->any())
                <div class="alert-danger alert-box">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @push('styles')
                <link rel="stylesheet" href="{{ asset('css/reservations.css') }}">
            @endpush

            <div class="summary-box mb-4">
                <div class="summary-row">
                    <span>Nomor Tiket:</span>
                    <strong class="text-primary-lg">{{ $reservation->ticket_code }}</strong>
                </div>
                <div class="summary-row">
                    <span>Tipe Kamar:</span>
                    <strong>{{ $reservation->roomType->name }}</strong>
                </div>
                <div class="summary-total summary-total-spaced">
                    <span>Total Tagihan yang Harus Dibayar:</span>
                    <span class="text-primary-lg">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="payment-bank-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span class="payment-bank-label-text">Rekening Tujuan Transfer</span>
            </div>
            <div class="bank-cards-container mb-4">
                @forelse($bankAccounts as $bank)
                    <div class="bank-card">
                        <div class="bank-name">{{ $bank->bank_name }}</div>
                        <div class="bank-account">{{ $bank->account_number }}</div>
                        <div class="bank-owner">a.n {{ $bank->account_name }}</div>
                    </div>
                @empty
                    <div class="alert alert-warning">Belum ada data rekening bank yang tersedia. Silakan hubungi admin.</div>
                @endforelse
            </div>

            <hr class="form-divider">

            <form action="{{ route('reservations.uploadPayment', $reservation->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-grid form-grid-1col">
                    <div>
                        <label for="bank_account_id" class="form-label">Transfer ke Rekening</label>
                        <select name="bank_account_id" id="bank_account_id" required class="custom-input">
                            <option value="">-- Pilih Rekening Tujuan Transfer Anda --</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="payment_proof" class="form-label">Unggah Bukti Transfer (JPG, PNG)</label>
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required class="custom-input custom-file-input">
                        <small class="form-note">*Maksimal ukuran file 2MB.</small>
                    </div>
                </div>

                <div class="flex-actions flex-actions-mt30">
                    <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-cancel">Nanti Saja</a>
                    <button type="submit" class="btn-confirm">
                        Kirim Bukti Pembayaran
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4 20-7z"/></svg>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
