<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ReservationChart extends ChartWidget
{
    protected ?string $heading = 'Tren Reservasi (6 Bulan Terakhir)';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            
            // Hitung total reservasi baru yang dibuat bulan ini (selain yg dibatalkan)
            $total = Reservation::where('status', '!=', 'cancelled')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
                
            $data[] = $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Reservasi (Di luar Batal)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)', // Emerald-500
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Grafik Batang
    }
}
