<?php

namespace App\Filament\Resources\Reservations\Widgets;

use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReservationStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Siap Check-In', Reservation::where('status', 'confirmed')->whereNotNull('room_id')->count())
                ->description('Telah Lunas & Diplot Kamar')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
                
            Stat::make('Butuh Alokasi Kamar', Reservation::where('status', 'confirmed')->whereNull('room_id')->count())
                ->description('Telah Lunas, Belum Diplot Kamar')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
                
            Stat::make('Menunggu Pembayaran', Reservation::where('status', 'pending')->count())
                ->description('Reservasi Dibuat, Belum Lunas')
                ->color('danger'),
        ];
    }
}
