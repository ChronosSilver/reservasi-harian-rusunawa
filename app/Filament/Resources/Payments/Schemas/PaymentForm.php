<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Relasi ke Reservasi
                \Filament\Forms\Components\Select::make('reservation_id')
                    ->relationship('reservation', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "Res ID: {$record->id} - {$record->user->name}")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Pilih Reservasi'),

                // 2. Data Uang
                \Filament\Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Nominal Pembayaran'),

                // 3. Metode & Bukti Fisik
                \Filament\Forms\Components\Select::make('payment_method')
                    ->required()
                    ->options([
                        'transfer' => 'Transfer Bank',
                        'cash' => 'Tunai (Cash)',
                    ])
                    ->live(debounce: 500)
                    ->label('Metode Pembayaran'),

                \Filament\Forms\Components\FileUpload::make('payment_proof')
                    ->directory('bukti-transfer')
                    ->disk('public')
                    ->image()
                    ->nullable()
                    // Filter Logika murni
                    ->required(fn ($get) => $get('payment_method') === 'transfer')
                    ->label('Foto Bukti Transfer (Abaikan jika tunai)'),

                // 4. Audit Waktu & Status
                \Filament\Forms\Components\DateTimePicker::make('payment_date')
                    ->nullable()
                    ->default(now())
                    ->label('Waktu Pembayaran Dilakukan'),

                \Filament\Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'pending' => 'Pending (Menunggu Validasi)',
                        'verified' => 'Verified (Sah/Lunas)',
                        'rejected' => 'Rejected (Ditolak/Palsu)',
                        'refunded' => 'Refunded (Dikembalikan)',
                    ])
                    ->default('pending')
                    ->label('Status Validasi'),
            ]);
    }
}