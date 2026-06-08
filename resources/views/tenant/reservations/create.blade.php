@extends('layouts.app')

@section('content')
<div class="container form-container min-h-80vh p-form">
    
    <!-- Tombol Kembali -->
    <a href="javascript:history.back()" class="btn-back">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Kembali
    </a>

    <div class="form-wrapper">
        
        <div class="form-header">
                <h2 class="h2-title">Formulir Reservasi Kamar</h2>
                <p class="subtitle-text">Silakan lengkapi detail pesanan Anda di bawah ini.</p>
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

                <form action="{{ route('reservations.store') }}" method="POST" id="reservationForm">
                    @csrf
                    
                    <div class="form-grid">
                        <!-- Baris 1 -->
                        <div>
                            <label for="room_type_id" class="form-label">Tipe Kamar</label>
                            <select name="room_type_id" id="room_type_id" required class="custom-input">
                                <option value="">-- Pilih Tipe Kamar --</option>
                                @foreach($roomTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        data-price="{{ $type->base_price }}" 
                                        data-extra="{{ $type->extra_person_fee }}"
                                        {{ (old('room_type_id') ?? $selectedType) == $type->id ? 'selected' : '' }}>
                                        Kamar {{ $type->name }} (Rp {{ number_format($type->base_price, 0, ',', '.') }}/hari)
                                    </option>
                                @endforeach
                            </select>
                            <div id="availability_info" class="mt-2" style="font-size: 0.85rem; font-weight: 500; display: none;">
                                <!-- Konten counter dari JS -->
                            </div>
                        </div>

                        <div>
                            <label for="check_in_date" class="form-label">Rencana Tanggal Masuk (Check-in)</label>
                            <input type="date" name="check_in_date" id="check_in_date" value="{{ old('check_in_date') }}" min="{{ date('Y-m-d') }}" required class="custom-input">
                        </div>

                        <!-- Baris 2 -->
                        <div>
                            <label for="guest_count" class="form-label">Rencana Jumlah Penghuni</label>
                            <select name="guest_count" id="guest_count" required class="custom-input">
                                <option value="1" {{ old('guest_count') == 1 ? 'selected' : '' }}>1 Orang</option>
                                <option value="2" {{ old('guest_count') == 2 ? 'selected' : '' }}>2 Orang (Kapasitas Ideal)</option>
                                <option value="3" {{ old('guest_count') == 3 ? 'selected' : '' }}>3 Orang (Dikenakan Biaya Ekstra)</option>
                            </select>
                        </div>

                        <div>
                            <label for="duration_days" class="form-label">Durasi Sewa (Hari)</label>
                            <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', 1) }}" min="1" max="365" required class="custom-input">
                            <small class="form-note">*Maksimal 365 Hari.</small>
                        </div>
                        
                        <!-- Baris 3 -->
                        <div class="full-width-col">
                            <label for="payment_method" class="form-label">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required class="custom-input">
                                <option value="" disabled selected>-- Pilih Metode Pembayaran --</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tunai (Bayar langsung di tempat)</option>
                            </select>
                        </div>
                    </div>

                    <hr class="form-divider">

                    <!-- Ringkasan Harga -->
                    <div class="summary-box">
                        <h4 class="summary-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Kalkulasi Biaya
                        </h4>
                        
                        <div class="summary-row">
                            <span>Biaya Sewa Kamar (<span id="calc_days" class="text-bold">1</span> Hari):</span>
                            <span id="summary_base" class="text-medium">Rp 0</span>
                        </div>
                        <div class="summary-row" id="extra_fee_row">
                            <span>Biaya Ekstra (Penghuni ke-3):</span>
                            <span id="summary_extra" class="text-medium">Rp 0</span>
                        </div>
                        <div class="summary-total">
                            <span>Total Tagihan:</span>
                            <span id="summary_total">Rp 0</span>
                        </div>
                    </div>

                    <div class="flex-actions">
                        <a href="{{ route('reservations.index') }}" class="btn btn-outline btn-cancel">Batalkan</a>
                        <button type="submit" class="btn-confirm">
                            Konfirmasi Pesanan
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/reservations.js') }}?v={{ filemtime(public_path('js/reservations.js')) }}"></script>
@endpush
@endsection
