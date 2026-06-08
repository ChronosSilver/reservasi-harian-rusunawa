@extends('layouts.app')

@section('content')
<div class="container dashboard-container min-h-80vh">
    <div class="dashboard-header">
        <h1>Riwayat Reservasi</h1>
        <p>Catatan seluruh perjalanan pemesanan kamar Anda.</p>
    </div>

    <div class="dashboard-panel">
        @if(isset($reservations) && $reservations->count() > 0)
            <div class="overflow-x-auto">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Kode Tiket</th>
                            <th>Tipe Kamar</th>
                            <th>Tanggal Pesan</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $reservation)
                            @php
                                $payment = $reservation->payments->first();
                                $paymentData = null;
                                if ($payment) {
                                    $paymentData = [
                                        'status' => strtoupper($payment->status),
                                        'date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y H:i') : '-',
                                        'method' => 'Transfer Bank', // Bisa dinamis jika ada metode lain
                                        'bank' => $payment->bankAccount ? $payment->bankAccount->bank_name . ' - ' . $payment->bankAccount->account_number : '-',
                                        'proof' => $payment->payment_proof ? asset('storage/' . $payment->payment_proof) : null,
                                        'amount' => 'Rp ' . number_format($payment->amount ?? $reservation->total_price, 0, ',', '.'),
                                        'refund_proof' => $payment->refund_proof ? asset('storage/' . $payment->refund_proof) : null,
                                    ];
                                }
                                
                                $statusClass = 'pending';
                                if(in_array($reservation->status, ['confirmed', 'active'])) $statusClass = 'confirmed';
                                elseif($reservation->status == 'completed') $statusClass = 'completed';
                                elseif($reservation->status == 'cancelled') $statusClass = 'cancelled';
                                elseif($reservation->status == 'refunding') $statusClass = 'refunding';
                                
                                $historyData = [
                                    'ticket' => $reservation->ticket_code,
                                    'room' => $reservation->roomType->name,
                                    'checkIn' => \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y'),
                                    'checkOut' => \Carbon\Carbon::parse($reservation->check_in_date)->addDays($reservation->duration_days)->format('d M Y'),
                                    'duration' => $reservation->duration_days . ' Hari',
                                    'total' => 'Rp ' . number_format($reservation->total_price, 0, ',', '.'),
                                    'status' => strtoupper($reservation->status),
                                    'statusClass' => 'badge-' . $statusClass,
                                    'payment' => $paymentData
                                ];
                            @endphp
                            <tr class="clickable-row js-history-row" data-history="{{ json_encode($historyData) }}">
                                <td>{{ $reservation->ticket_code }}</td>
                                <td>{{ $reservation->roomType->name }}</td>
                                <td>{{ $reservation->created_at->format('d M Y') }}</td>
                                <td>Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ strtoupper($reservation->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Modal Detail Riwayat -->
            <div class="modal-overlay" id="historyDetailModal">
                <div class="modal-content modal-wide js-stop-prop">
                    <div class="modal-header">
                        <h3 class="modal-title">Detail Riwayat <span id="hist-ticket" class="modal-ticket-text"></span></h3>
                        <button class="modal-close js-close-history-modal">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <div class="modal-body modal-scrollable-body">
                        <h4 class="modal-section-title">Informasi Reservasi</h4>
                        <div class="modal-status-row">
                            <span class="modal-detail-label">Status Reservasi</span>
                            <span class="badge" id="hist-status"></span>
                        </div>
                        <div class="modal-detail-row">
                            <span class="modal-detail-label">Tipe Kamar</span>
                            <span class="modal-detail-value" id="hist-room"></span>
                        </div>
                        <div class="modal-detail-row">
                            <span class="modal-detail-label">Tanggal Check-in</span>
                            <span class="modal-detail-value" id="hist-checkin"></span>
                        </div>
                        <div class="modal-detail-row">
                            <span class="modal-detail-label">Tanggal Check-out</span>
                            <span class="modal-detail-value" id="hist-checkout"></span>
                        </div>
                        <div class="modal-detail-row">
                            <span class="modal-detail-label">Durasi Sewa</span>
                            <span class="modal-detail-value" id="hist-duration"></span>
                        </div>
                        <div class="modal-total-row mb-20px">
                            <span class="modal-total-label">Total Harga</span>
                            <span class="modal-total-value" id="hist-total"></span>
                        </div>
                        
                        <h4 class="modal-section-title">Informasi Pembayaran</h4>
                        <div id="hist-payment-container">
                            <!-- Injeksi via JS -->
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-mb-15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <p>Belum ada riwayat pemesanan yang tercatat.</p>
            </div>
        @endif
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reservations.css') }}?v={{ filemtime(public_path('css/reservations.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/reservations.js') }}?v={{ filemtime(public_path('js/reservations.js')) }}"></script>
@endpush
@endsection
