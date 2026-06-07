<?php

namespace App\Filament\Resources\Payments\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Hitung total pendapatan dari pembayaran yang sudah masuk
        $totalRevenue = Payment::whereIn('status', ['paid', 'verified'])->sum('amount');
        
        // Hitung antrean yang perlu dicek
        $pendingPayments = Payment::where('status', 'pending')->count();
        $paidPayments = Payment::where('status', 'paid')->count();
        $totalQueue = $pendingPayments + $paidPayments;

        // Hitung yang di-refund
        $refundedPayments = Payment::where('status', 'refunded')->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Dari pembayaran masuk/terverifikasi')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Antrean Pembayaran', $totalQueue)
                ->description($paidPayments . ' butuh verifikasi, ' . $pendingPayments . ' belum bayar')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([3, 1, 2, 5, 2, 6, 4]),

            Stat::make('Total Refund', $refundedPayments)
                ->description('Pembayaran yang dikembalikan')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger')
                ->chart([0, 1, 0, 2, 1, 0, 1]),
        ];
    }
}
