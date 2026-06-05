<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Tables\Table;
use App\Models\Payment;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('reservation.user.name')
                    ->label('Nama Penyewa')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('reservation.payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'transfer' => 'info',
                        'cash' => 'success',
                        default => 'gray',
                    }),

                \Filament\Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti'),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'info',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                    
                \Filament\Tables\Columns\TextColumn::make('payment_date')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid (Menunggu Verifikasi)',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActionsColumnLabel(new \Illuminate\Support\HtmlString('<div style="text-align: center; width: 100%;">Aksi</div>'))
            ->actions([
                // MENGGUNAKAN FILAMENT\ACTIONS MURNI SESUAI INSTRUKSI ANDA
                \Filament\Actions\Action::make('verify')
                    ->label('Terima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->modalDescription('Apakah dana sudah benar-benar masuk? Tindakan ini akan mencatat waktu pembayaran dan mengubah status reservasi.')
                    ->disabled(function (Payment $record) {
                        $method = $record->reservation->payment_method ?? 'transfer';
                        if ($method === 'transfer') {
                            // Jika transfer, tombol Terima hanya aktif jika statusnya 'paid'
                            return $record->status !== 'paid';
                        }
                        // Jika tunai, tombol Terima aktif saat 'pending' atau 'paid'
                        return !in_array($record->status, ['pending', 'paid']);
                    })
                    ->action(function (Payment $record) {
                        $record->update([
                            'status' => 'verified',
                            'payment_date' => now(),
                        ]);
                        $record->reservation->update(['status' => 'confirmed']);
                    }),

                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->modalDescription('Apakah bukti transfer palsu atau dana tidak masuk?')
                    ->disabled(fn (Payment $record) => !in_array($record->status, ['pending', 'paid']))
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'rejected']);
                        $record->reservation->update(['status' => 'cancelled']);
                    }),

                \Filament\Actions\Action::make('refund')
                    ->label('Kembalikan Dana')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kembalikan Dana (Refund)')
                    ->modalDescription('Apakah Anda yakin ingin melakukan refund pada pembayaran ini? Status reservasi juga akan dibatalkan.')
                    ->disabled(fn (Payment $record) => $record->status !== 'verified')
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'refunded']);
                        $record->reservation->update(['status' => 'cancelled']);
                    }),

                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}