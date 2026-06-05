<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. INPUT RELASI
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Nama Penyewa'),

                Select::make('room_type_id')
                    ->relationship('roomType', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ( $set) => $set('room_id', null))
                    ->label('Tipe Kamar'),

                Select::make('room_id')
                    ->nullable()
                    ->placeholder(fn ($get) => (!$get('check_in_date') || !$get('check_out_date') || !$get('room_type_id')) ? 'Pilih Tipe & Tanggal Dulu' : 'Pilih Kamar Tersedia')
                    ->label('Nomor Kamar')
                    ->relationship(
                        name: 'room',
                        titleAttribute: 'room_number',
                        modifyQueryUsing: function ($query, $get) {
                            $typeId = $get('room_type_id');
                            $checkIn = $get('check_in_date');
                            $checkOut = $get('check_out_date');
                            // Jika tipe atau tanggal belum diisi, kosongkan pilihan (keamanan paksa)
                            if (!$typeId || !$checkIn || !$checkOut) {
                                return $query->whereRaw('1 = 0'); 
                            }
                            // Filter 1: Sesuai Tipe dan Secara Fisik TIDAK Maintenance
                            $query->where('room_type_id', $typeId)
                                  ->where('status', '!=', 'maintenance');
                            // Filter 2: Sub-Query Anti Double Booking
                            // Menyingkirkan kamar yang ID-nya ada di tabel reservasi pada tanggal yang beririsan
                            $query->whereNotIn('id', function ($subQuery) use ($checkIn, $checkOut) {
                                $subQuery->select('room_id')
                                    ->from('reservations')
                                    ->whereNotNull('room_id')
                                    ->whereIn('status', ['pending', 'confirmed', 'active']) // Hanya cek reservasi yang valid
                                    ->where('check_in_date', '<', $checkOut)   // Logika Irisan Waktu
                                    ->where('check_out_date', '>', $checkIn);  // Logika Irisan Waktu
                            });
                            return $query;
                        }
                    ),

                // 2. INPUT KONTRAK WAKTU & FINANSIAL
                DatePicker::make('check_in_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('room_id', null))
                    ->label('Rencana Check-In'),

                DatePicker::make('check_out_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('room_id', null))
                    ->label('Rencana Check-Out'),

                TextInput::make('guest_count')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(3) // Batasan ketat aturan Rusunawa
                    ->default(1)
                    ->label('Jumlah Tamu'),

                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->label('Total Harga Kontrak'),

                // 3. INPUT SIKLUS HIDUP OPERASIONAL
                Select::make('status')
                    ->required()
                    ->options([
                        'pending' => 'Pending (Menunggu Pembayaran)',
                        'confirmed' => 'Confirmed (Sudah Bayar)',
                        'active' => 'Active (Sedang Dihuni)',
                        'completed' => 'Completed (Selesai)',
                        'cancelled' => 'Cancelled (Dibatalkan)',
                    ])
                    ->default('pending')
                    ->label('Status Reservasi'),

                Textarea::make('notes')
                    ->nullable()
                    ->columnSpanFull()
                    ->label('Catatan Lapangan (Opsional)'),
                    
                // 4. INFORMASI PEMBAYARAN (STATIS/READ-ONLY)
                TextInput::make('payment_date_static')
                    ->label('Tanggal Pembayaran Terakhir')
                    ->disabled()
                    ->formatStateUsing(function ($state, $record) {
                        $payment = $record ? $record->payments()->latest()->first() : null;
                        return $payment ? $payment->payment_date : '-';
                    })
                    ->visible(fn ($record) => $record !== null),
                    
                TextInput::make('payment_amount_static')
                    ->label('Nominal Pembayaran Terakhir')
                    ->disabled()
                    ->formatStateUsing(function ($state, $record) {
                        $payment = $record ? $record->payments()->latest()->first() : null;
                        return $payment ? 'Rp ' . number_format($payment->amount, 0, ',', '.') : '-';
                    })
                    ->visible(fn ($record) => $record !== null),
                    
                TextInput::make('payment_method_static')
                    ->label('Metode Pembayaran')
                    ->disabled()
                    ->formatStateUsing(function ($state, $record) {
                        $payment = $record ? $record->payments()->latest()->first() : null;
                        $method = $payment ? $payment->payment_method : '-';
                        return is_string($method) ? ucfirst($method) : $method;
                    })
                    ->visible(fn ($record) => $record !== null),
                    
                TextInput::make('payment_status_static')
                    ->label('Status Validasi Pembayaran')
                    ->disabled()
                    ->formatStateUsing(function ($state, $record) {
                        $payment = $record ? $record->payments()->latest()->first() : null;
                        $status = $payment ? $payment->status : '-';
                        return is_string($status) ? ucfirst($status) : $status;
                    })
                    ->visible(fn ($record) => $record !== null),
                    
                \Filament\Forms\Components\FileUpload::make('payment_proof_static')
                    ->label('Bukti Transfer Terakhir')
                    ->image()
                    ->disabled() // Mode baca (read-only)
                    ->columnSpanFull()
                    ->formatStateUsing(function ($state, $record) {
                        $payment = $record ? $record->payments()->latest()->first() : null;
                        return $payment ? $payment->payment_proof : null;
                    })
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}