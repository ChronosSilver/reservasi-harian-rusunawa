<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class IncomeChart extends ChartWidget
{
    protected ?string $heading = 'Pendapatan (6 Bulan Terakhir)';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            
            // Hitung total uang masuk yang sudah terverifikasi bulan ini
            $total = Payment::where('status', 'verified')
                ->whereYear('verified_at', $month->year)
                ->whereMonth('verified_at', $month->month)
                ->sum('amount');
                
            $data[] = $total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan (Rp)',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)', // Amber-500
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'tension' => 0.4, // Membuat kurva lebih smooth
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
