<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Pembayaran')
                    ->description('Formulir pencatatan dan validasi pembayaran.')
                    ->schema([
                        // 1. Relasi ke Reservasi
                        \Filament\Forms\Components\Select::make('reservation_id')
                            ->relationship('reservation', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $reservation = \App\Models\Reservation::find($state);
                                    if ($reservation && $reservation->total_price !== null) {
                                        $set('amount', $reservation->total_price);
                                    } else {
                                        $set('amount', null); 
                                    }
                                } else {
                                    $set('amount', null);
                                }
                            })
                            ->label('Pilih Reservasi')
                            ->columnSpanFull(), // Dibuat 1 kolom penuh per baris

                        // 2. Data Uang (Dihitung Otomatis)
                        \Filament\Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly() // Tidak dapat di-input manual
                            ->placeholder('-') // Muncul simbol '-' jika kosong
                            ->label('Nominal Pembayaran')
                            ->columnSpanFull(), // Dibuat 1 kolom penuh per baris

                        // 3. Metode (Ditarik dari Reservasi, Read Only)
                        \Filament\Forms\Components\TextInput::make('payment_method_display')
                            ->label('Metode Pembayaran')
                            ->formatStateUsing(function ($record) {
                                if (!$record || !$record->reservation) return '-';
                                return $record->reservation->payment_method === 'transfer' ? 'Transfer Bank' : 'Tunai (Cash)';
                            })
                            ->readOnly()
                            ->columnSpanFull(),

                        // 4. Status Validasi
                        \Filament\Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'pending' => 'Pending (Menunggu Pembayaran)',
                                'paid' => 'Paid (Menunggu Verifikasi)',
                                'verified' => 'Verified (Sah/Lunas)',
                                'rejected' => 'Rejected (Ditolak/Palsu)',
                                'refunded' => 'Refunded (Dikembalikan)',
                            ])
                            ->default('pending')
                            ->disabled()
                            ->dehydrated()
                            ->label('Status Validasi Pembayaran')
                            ->columnSpanFull(), // Dibuat 1 kolom penuh per baris

                        // Waktu pembayaran disembunyikan (otomatis diisi oleh sistem)
                        \Filament\Forms\Components\Hidden::make('payment_date')
                            ->default(now()),

                        // 5. Bukti Fisik (Diletakkan Paling Bawah)
                        \Filament\Forms\Components\FileUpload::make('payment_proof')
                            ->directory('bukti-transfer')
                            ->disk('public')
                            ->image()
                            ->nullable()
                            ->required(fn ($get) => $get('payment_method') === 'transfer' && $get('status') === 'verified')
                            ->label('Foto Bukti Transfer (Abaikan jika tunai)')
                            ->columnSpanFull(), // Dibuat 1 kolom penuh per baris
                    ]),
            ]);
    }
}