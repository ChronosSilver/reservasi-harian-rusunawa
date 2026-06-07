@extends('layouts.app')

@section('content')
<div class="container dashboard-container min-h-80vh">
    <div class="dashboard-header flex-header">
        <div>
            <h1>Reservasi Aktif</h1>
            <p class="mb-0">Pantau status pesanan kamar Anda yang sedang berjalan.</p>
        </div>
        <a href="{{ route('reservations.create') }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-mr-8"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Buat Reservasi Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success-custom">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-danger alert-box">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-panel">
        @if(isset($reservations) && $reservations->count() > 0)
            <div class="reservation-list">
                @foreach($reservations as $reservation)
                    <div class="reservation-card" onclick="openReservationModal({
                        ticket: '{{ $reservation->ticket_code }}',
                        room: '{{ $reservation->roomType->name }}',
                        checkIn: '{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y') }}',
                        checkOut: '{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M Y') }}',
                        duration: '{{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} Hari',
                        payment: '{{ $reservation->payment_method == 'transfer' ? 'Transfer Bank' : 'Tunai' }}',
                        total: 'Rp {{ number_format($reservation->total_price, 0, ',', '.') }}',
                        status: '{{ strtoupper($reservation->status) }}',
                        statusClass: 'badge-{{ strtolower($reservation->status) }}'
                    })">
                        
                        <div class="flex-1-min-280">
                            <div class="flex-center-gap">
                                <strong class="text-primary-lg">{{ $reservation->ticket_code }}</strong>
                                <span class="badge badge-{{ strtolower($reservation->status) }}">
                                    {{ strtoupper($reservation->status) }}
                                </span>
                            </div>
                            
                            <div class="reservation-card-info">
                                <div class="info-group">
                                    <span>Tipe Kamar</span>
                                    <strong>{{ $reservation->roomType->name }}</strong>
                                </div>
                                <div class="info-group">
                                    <span>Check-in</span>
                                    <strong>{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y') }}</strong>
                                </div>
                                <div class="info-group">
                                    <span>Durasi</span>
                                    <strong>{{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} Hari</strong>
                                </div>
                                <div class="info-group">
                                    <span>Metode Pembayaran</span>
                                    <strong>{{ $reservation->payment_method == 'transfer' ? 'Transfer Bank' : 'Tunai' }}</strong>
                                </div>
                                <div class="info-group">
                                    <span>Total Tagihan</span>
                                    <strong class="text-primary-md">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="text-right-min-200">
                            @if($reservation->status == 'pending')
                                <div class="flex-actions" style="margin-top:0; justify-content: flex-end;">
                                    <button type="button" class="btn btn-outline btn-cancel" style="border-color: #e11d48; color: #e11d48;" onclick="event.stopPropagation(); openCancelModal('{{ $reservation->id }}', '{{ $reservation->ticket_code }}')">Batalkan</button>
                                    
                                    <a href="#" class="btn btn-primary btn-action-primary" onclick="event.stopPropagation();">
                                        Lanjutkan ke Pembayaran
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-ml-5-vert"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </a>
                                </div>
                            @else
                                <button class="btn btn-outline btn-action-outline" onclick="event.stopPropagation(); openReservationModal({
                                    ticket: '{{ $reservation->ticket_code }}',
                                    room: '{{ $reservation->roomType->name }}',
                                    checkIn: '{{ \Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y') }}',
                                    checkOut: '{{ \Carbon\Carbon::parse($reservation->check_out_date)->format('d M Y') }}',
                                    duration: '{{ \Carbon\Carbon::parse($reservation->check_in_date)->diffInDays(\Carbon\Carbon::parse($reservation->check_out_date)) }} Hari',
                                    payment: '{{ $reservation->payment_method == 'transfer' ? 'Transfer Bank' : 'Tunai' }}',
                                    total: 'Rp {{ number_format($reservation->total_price, 0, ',', '.') }}',
                                    status: '{{ strtoupper($reservation->status) }}',
                                    statusClass: 'badge-{{ strtolower($reservation->status) }}'
                                })">Lihat Detail</button>
                            @endif
                        </div>
                        
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon-mb-15"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <p>Anda belum memiliki reservasi kamar yang aktif saat ini.</p>
                <a href="{{ route('reservations.create') }}" class="btn btn-primary">Buat Reservasi Baru</a>
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Reservasi -->
<div class="modal-overlay" id="reservationModal" onclick="closeReservationModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="modal-title">Detail Reservasi <span id="modal-ticket" class="modal-ticket-text"></span></h3>
            <button class="modal-close" onclick="closeReservationModal()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-status-row">
                <span class="modal-detail-label">Status</span>
                <span class="badge" id="modal-status"></span>
            </div>
            
            <div class="modal-detail-row">
                <span class="modal-detail-label">Tipe Kamar</span>
                <span class="modal-detail-value" id="modal-room"></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Tanggal Check-in</span>
                <span class="modal-detail-value" id="modal-checkin"></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Tanggal Check-out</span>
                <span class="modal-detail-value" id="modal-checkout"></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Durasi Sewa</span>
                <span class="modal-detail-value" id="modal-duration"></span>
            </div>
            <div class="modal-detail-row">
                <span class="modal-detail-label">Metode Pembayaran</span>
                <span class="modal-detail-value" id="modal-payment"></span>
            </div>
            
            <div class="modal-total-row">
                <span class="modal-total-label">Total Tagihan</span>
                <span id="modal-total" class="modal-total-value"></span>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembatalan Reservasi -->
<div class="modal-overlay" id="cancelModal" onclick="closeCancelModal()">
    <div class="modal-content" onclick="event.stopPropagation()">
        <form id="cancelForm" action="" method="POST">
            @csrf
            <div class="modal-header" style="border-bottom-color: #fecdd3; background-color: #fff1f2; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                <h3 class="modal-title" style="color: #be123c; font-weight: 600;">Pembatalan Reservasi</h3>
                <button type="button" class="modal-close" onclick="closeCancelModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="cancel-warning-box">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        <strong>Peringatan!</strong>
                        <p class="mb-0" style="margin-top:4px;">Anda akan membatalkan tiket <strong id="cancel-ticket-code"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div style="margin-bottom: 5px;">
                    <label for="cancellation_reason" class="modal-detail-label" style="color: #334155; font-weight: 500;">Alasan Pembatalan <span style="color:#e11d48;">*</span></label>
                    <textarea name="cancellation_reason" id="cancellation_reason" class="cancel-textarea" placeholder="Contoh: Salah pilih tanggal, Berhalangan hadir, dsb." required></textarea>
                </div>
            </div>
            <div class="modal-header" style="background-color: #f8fafc; border-top: 1px solid var(--border-color); border-bottom: none; border-radius: 0 0 var(--radius-lg) var(--radius-lg); padding: 15px 25px; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline btn-cancel" onclick="closeCancelModal()">Tutup</button>
                <button type="submit" class="btn btn-primary" style="background-color: #e11d48; border-color: #e11d48; border-radius: 30px; padding: 10px 20px;">Ya, Batalkan Reservasi</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/reservations.css') }}">
@endpush

@push('scripts')
<script>
    function openReservationModal(data) {
        document.getElementById('modal-ticket').innerText = data.ticket;
        document.getElementById('modal-room').innerText = data.room;
        document.getElementById('modal-checkin').innerText = data.checkIn;
        document.getElementById('modal-checkout').innerText = data.checkOut;
        document.getElementById('modal-duration').innerText = data.duration;
        document.getElementById('modal-payment').innerText = data.payment;
        document.getElementById('modal-total').innerText = data.total;
        
        const statusEl = document.getElementById('modal-status');
        statusEl.innerText = data.status;
        statusEl.className = 'badge ' + data.statusClass;
        
        document.getElementById('reservationModal').classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeReservationModal() {
        document.getElementById('reservationModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function openCancelModal(id, ticket) {
        document.getElementById('cancel-ticket-code').innerText = ticket;
        // Construct the URL dynamically for the form action
        const form = document.getElementById('cancelForm');
        form.action = `/reservations/${id}/cancel`;
        
        document.getElementById('cancelModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.remove('active');
        document.body.style.overflow = '';
        document.getElementById('cancelForm').reset();
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeReservationModal();
            closeCancelModal();
        }
    });
</script>
@endpush
@endsection
