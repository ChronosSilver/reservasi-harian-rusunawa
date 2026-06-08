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
                \Filament\Tables\Columns\TextColumn::make('payment_code')
                    ->label('Kode Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('reservation.ticket_code')
                    ->label('Kode Tiket')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),
                    
                \Filament\Tables\Columns\TextColumn::make('reservation.user.name')
                    ->label('Nama Penyewa')
                    ->searchable()
                    ->toggleable(),

                \Filament\Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(),

                \Filament\Tables\Columns\TextColumn::make('bankAccount.bank_name')
                    ->label('Bank Tujuan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                \Filament\Tables\Columns\TextColumn::make('reservation.payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'transfer' => 'Transfer Bank',
                        'cash' => 'Tunai',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'transfer' => 'info',
                        'cash' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),

                \Filament\Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti')
                    ->toggleable(),

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
                    })
                    ->toggleable(),
                    
                \Filament\Tables\Columns\TextColumn::make('payment_date')
                    ->label('Dibayar Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                \Filament\Tables\Columns\TextColumn::make('verified_at')
                    ->label('Diverifikasi Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->recordActionsColumnLabel('')
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('verify')
                        ->label('Terima')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi Pembayaran')
                        ->modalDescription('Apakah dana sudah benar-benar masuk? Tindakan ini akan mencatat waktu verifikasi dan mengubah status reservasi.')
                        ->disabled(fn (Payment $record) => !in_array($record->status, ['pending', 'paid']))
                        ->action(function (Payment $record) {
                            $record->update([
                                'status' => 'verified',
                                'verified_at' => now(), // Catat waktu verifikasi
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

                    // Aksi refund dan cancel_refund kini dikendalikan secara terpusat melalui Header Actions di halaman Edit Reservasi.

                    \Filament\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    // \Filament\Actions\DeleteBulkAction::make(), // Dinonaktifkan untuk menjaga riwayat transaksi
                ]),
            ]);
    }
}