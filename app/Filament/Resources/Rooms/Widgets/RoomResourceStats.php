<?php

namespace App\Filament\Resources\Rooms\Widgets;

use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoomResourceStats extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Kamar Yang Ada', Room::count())
                ->description('Total seluruh unit kamar')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('gray'),

            Stat::make('Kamar Belum Direservasi', Room::whereDoesntHave('reservations', function ($query) {
                    $query->whereIn('status', ['pending', 'confirmed', 'active']);
                })->count())
                ->description('Kamar tanpa penyewa aktif')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),

            Stat::make('Status Available', Room::where('status', 'available')->count())
                ->description('Kamar yang siap huni')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Kamar Ditempati', Room::where('status', 'occupied')->count())
                ->description('Kamar sedang disewa')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning'),
        ];
    }
}
