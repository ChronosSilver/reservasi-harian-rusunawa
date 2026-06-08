<?php

namespace App\Filament\Resources\RoomTypes\Widgets;

use App\Models\RoomType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoomTypeStats extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $roomTypes = RoomType::withCount('rooms')->get();
        
        foreach ($roomTypes as $type) {
            $stats[] = Stat::make('Total Kamar ' . $type->name, $type->rooms_count . ' Unit')
                ->description('Jumlah fisik kamar tipe ' . $type->name)
                ->descriptionIcon('heroicon-m-home')
                ->color('primary');
        }

        return $stats;
    }
}
