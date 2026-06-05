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
    public static function calculateTotalPrice(callable $set, callable $get)
    {
        $roomTypeId = $get('room_type_id');
        $checkIn = $get('check_in_date');
        $checkOut = $get('check_out_date');
        $guestCount = (int) $get('guest_count');

        if ($roomTypeId && $checkIn && $checkOut) {
            $roomType = \App\Models\RoomType::find($roomTypeId);
            if ($roomType) {
                $checkInDate = \Carbon\Carbon::parse($checkIn);
                $checkOutDate = \Carbon\Carbon::parse($checkOut);
                $days = $checkInDate->diffInDays($checkOutDate);
                
                if ($days > 0) {
                    $dailyPrice = $roomType->base_price;
                    if ($guestCount > 2) { // Beban biaya ekstra jika tamu lebih dari 2
                        $dailyPrice += $roomType->extra_person_fee;
                    }
                    $set('total_price', $dailyPrice * $days);
                } else {
                    $set('total_price', 0);
                }
            }
        } else {
            $set('total_price', null);
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Reservasi')
                    ->description('Detail penyewa, kamar, dan jadwal inap.')
                    ->schema([
                        // 1. INPUT RELASI
                        Select::make('user_id')
                            ->relationship('user', 'name', fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('role', 'penyewa'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Nama Penyewa'),

                        Select::make('room_type_id')
                            ->relationship('roomType', 'name')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('room_id', null);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Tipe Kamar'),

                        // 2. INPUT KONTRAK WAKTU
                        DatePicker::make('check_in_date')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('room_id', null);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Rencana Check-In'),

                        DatePicker::make('check_out_date')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('room_id', null);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Rencana Check-Out'),

                        // 3. NOMOR KAMAR (Dipindah ke bawah tanggal)
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
                                    if (!$typeId || !$checkIn || !$checkOut) {
                                        return $query->whereRaw('1 = 0'); 
                                    }
                                    $query->where('room_type_id', $typeId)
                                          ->where('status', '!=', 'maintenance');
                                    $query->whereNotIn('id', function ($subQuery) use ($checkIn, $checkOut) {
                                        $subQuery->select('room_id')
                                            ->from('reservations')
                                            ->whereNotNull('room_id')
                                            ->whereIn('status', ['pending', 'confirmed', 'active'])
                                            ->where('check_in_date', '<', $checkOut)
                                            ->where('check_out_date', '>', $checkIn);
                                    });
                                    return $query;
                                }
                            ),

                        // 4. KEUANGAN & TAMU
                        TextInput::make('guest_count')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(3)
                            ->default(1)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Jumlah Tamu'),

                        TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly() // Dibuat read-only karena dihitung otomatis
                            ->label('Total Harga Kontrak'),

                        // 5. INPUT SIKLUS HIDUP OPERASIONAL
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
                            ->label('Status Reservasi')
                            ->hidden(fn ($record) => $record === null)
                            ->disabled()
                            ->dehydrated(),

                        Select::make('payment_method')
                            ->options([
                                'transfer' => 'Transfer Bank',
                                'cash' => 'Tunai (Cash)',
                            ])
                            ->required()
                            ->default('transfer')
                            ->label('Metode Pembayaran')
                            ->disabled(fn ($record) => $record !== null)
                            ->dehydrated() // Pastikan nilainya tetap dikirim meski disabled
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->nullable()
                            ->columnSpanFull()
                            ->label('Catatan Lapangan (Opsional)'),
                    ])->columns(2),
                    
                // 6. DETAIL PEMBAYARAN (Bisa Diedit Langsung di Sini)
                \Filament\Forms\Components\Repeater::make('payments')
                    ->relationship('payments')
                    ->label('Detail Tagihan & Pembayaran')
                    ->addable(false) // Tidak bisa tambah tagihan manual dari sini (karena sudah otomatis terbuat)
                    ->deletable(false) // Tidak bisa dihapus untuk menjaga riwayat
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('payment_date')
                            ->label('Tanggal Pembayaran Terakhir')
                            ->disabled(), // Tetap tidak bisa diedit manual sesuai permintaan

                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending (Menunggu Pembayaran)',
                                'paid' => 'Paid (Menunggu Verifikasi)',
                                'verified' => 'Verified (Sah/Lunas)',
                                'rejected' => 'Rejected (Ditolak/Palsu)',
                                'refunded' => 'Refunded (Dikembalikan)',
                            ])
                            ->label('Status Validasi Pembayaran')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        \Filament\Forms\Components\FileUpload::make('payment_proof')
                            ->label('Upload Bukti Transfer')
                            ->image()
                            ->directory('bukti-transfer')
                            ->columnSpanFull(),
                            
                        \Filament\Schemas\Components\Actions::make([
                            \Filament\Actions\Action::make('verify')
                                ->label('Terima')
                                ->color('success')
                                ->requiresConfirmation()
                                ->action(function (callable $set, callable $get) {
                                    $set('status', 'verified');
                                    $set('payment_date', now()->toDateTimeString());
                                    $set('../../status', 'confirmed');
                                })
                                ->disabled(function (callable $get) {
                                    $status = $get('status');
                                    $method = $get('../../payment_method');
                                    if ($method === 'transfer') {
                                        return $status !== 'paid';
                                    }
                                    return !in_array($status, ['pending', 'paid']);
                                }),
                            \Filament\Actions\Action::make('reject')
                                ->label('Tolak')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action(function (callable $set) {
                                    $set('status', 'rejected');
                                    $set('../../status', 'cancelled');
                                })
                                ->disabled(function (callable $get) {
                                    $status = $get('status');
                                    $method = $get('../../payment_method');
                                    if ($method === 'transfer') {
                                        return $status !== 'paid';
                                    }
                                    return !in_array($status, ['pending', 'paid']);
                                }),
                            \Filament\Actions\Action::make('refund')
                                ->label('Kembalikan Dana')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action(function (callable $set) {
                                    $set('status', 'refunded');
                                    $set('../../status', 'cancelled');
                                })
                                ->disabled(function (callable $get) {
                                    return $get('status') !== 'verified';
                                }),
                        ])->columnSpanFull(),
                    ])
                    ->columns(3) // Agar Tanggal, Metode, dan Status sejajar dalam 1 baris
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record !== null), // Hanya muncul di halaman Edit, bukan Create
            ]);
    }
}