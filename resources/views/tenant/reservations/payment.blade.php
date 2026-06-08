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
                <style>
                    .bank-cards-container {
                        display: flex;
                        gap: 15px;
                        overflow-x: auto;
                        padding-bottom: 10px;
                    }
                    .bank-cards-container::-webkit-scrollbar {
                        height: 6px;
                    }
                    .bank-cards-container::-webkit-scrollbar-thumb {
                        background-color: #cbd5e1;
                        border-radius: 10px;
                    }
                    .bank-card {
                        border: 1px solid var(--border-color);
                        border-radius: var(--radius-md);
                        padding: 15px;
                        background: #f8fafc;
                        min-width: 250px;
                        flex: 0 0 auto;
                    }
                    .bank-name { font-weight: 700; color: var(--primary-color); font-size: 1.1rem; }
                    .bank-account { font-size: 1.25rem; letter-spacing: 1px; font-weight: 600; margin: 5px 0; color: #334155; }
                    .bank-owner { color: #64748b; font-size: 0.9rem; }
                    
                    .file-upload-wrapper {
                        border: 2px dashed #cbd5e1;
                        padding: 30px;
                        text-align: center;
                        border-radius: var(--radius-md);
                        cursor: pointer;
                        background: #f8fafc;
                        transition: all 0.2s ease;
                    }
                    .file-upload-wrapper:hover {
                        border-color: var(--primary-color);
                        background: #f0fdfa;
                    }
                </style>
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
                <div class="summary-total" style="margin-top: 10px; padding-top: 10px;">
                    <span>Total Tagihan yang Harus Dibayar:</span>
                    <span class="text-primary-lg">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</span>
                </div>
            </div>

            <div style="margin-top: 40px; margin-bottom: 20px; display: inline-flex; align-items: center; gap: 10px; background-color: #f1f5f9; padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary-color)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span style="font-size: 0.9rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Rekening Tujuan Transfer</span>
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
                
                <div class="form-grid" style="grid-template-columns: 1fr;">
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
                        <input type="file" name="payment_proof" id="payment_proof" accept="image/*" required class="custom-input" style="padding: 10px;">
                        <small class="form-note">*Maksimal ukuran file 2MB.</small>
                    </div>
                </div>

                <div class="flex-actions" style="margin-top: 30px;">
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
