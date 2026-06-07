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
                            <tr>
                                <td>{{ $reservation->ticket_code }}</td>
                                <td>{{ $reservation->roomType->name }}</td>
                                <td>{{ $reservation->created_at->format('d M Y') }}</td>
                                <td>Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ strtolower($reservation->status) }}">
                                        {{ strtoupper($reservation->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
    <link rel="stylesheet" href="{{ asset('css/reservations.css') }}">
@endpush
@endsection
