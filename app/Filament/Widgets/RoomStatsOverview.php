<?php
namespace App\Filament\Widgets;
use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoomStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // 1. Metrik Uang Masuk (Siap Jual)
            Stat::make('Kamar Tersedia', Room::where('status', 'available')->count())
                ->description('Siap untuk disewakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            // 2. Metrik Operasional Berjalan
            Stat::make('Kamar Dihuni', Room::where('status', 'occupied')->count())
                ->description('Sedang disewa oleh tamu')
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),
                
            // 3. Metrik Transisi (Tugas OB)
            Stat::make('Butuh Pembersihan', Room::where('status', 'cleaning')->count())
                ->description('Menunggu petugas kebersihan')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            // 4. Metrik Kebocoran Aset (Tugas Teknisi)
            Stat::make('Dalam Perawatan', Room::where('status', 'maintenance')->count())
                ->description('Butuh perbaikan teknisi')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
        ];
    }
}