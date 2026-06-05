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
                \Filament\Tables\Columns\TextColumn::make('reservation.id')
                    ->label('ID Res')
                    ->sortable()
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('reservation.user.name')
                    ->label('Nama Penyewa')
                    ->searchable(),

                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('payment_method')
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
                    ->label('Status Validasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                    
                \Filament\Tables\Columns\TextColumn::make('payment_date')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                // MENGGUNAKAN FILAMENT\ACTIONS MURNI SESUAI INSTRUKSI ANDA
                \Filament\Actions\Action::make('verify')
                    ->label('Terima Pembayaran')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->modalDescription('Apakah dana sudah benar-benar masuk? Tindakan ini akan mengubah status reservasi menjadi Confirmed secara otomatis.')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'verified']);
                        $record->reservation->update(['status' => 'confirmed']);
                    }),

                \Filament\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pembayaran')
                    ->modalDescription('Apakah bukti transfer palsu atau dana tidak masuk?')
                    ->visible(fn (Payment $record) => $record->status === 'pending')
                    ->action(function (Payment $record) {
                        $record->update(['status' => 'rejected']);
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