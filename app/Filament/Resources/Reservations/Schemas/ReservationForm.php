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
                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Informasi Reservasi')
                        ->description('Detail penyewa, kamar, dan jadwal inap.')
                        ->schema([
                        TextInput::make('ticket_code')
                            ->label('Kode Tiket Reservasi')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->hidden(fn ($record) => $record === null),
                        // 1. INPUT RELASI
                        Select::make('user_id')
                            ->relationship('user', 'name', fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('role', 'penyewa'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Nama Penyewa')
                            ->createOptionAction(fn ($action) => $action->modalWidth('md'))
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Nama Lengkap')
                                    ->maxLength(255),
                                Select::make('identity_type')
                                    ->options([
                                        'NIM' => 'NIM',
                                        'NIP' => 'NIP',
                                        'NIK' => 'NIK',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->label('Tipe Identitas'),
                                TextInput::make('identity_number')
                                    ->required()
                                    ->unique('users', 'identity_number')
                                    ->label('Nomor Identitas (NIM/NIP/NIK)')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->unique('users', 'email')
                                    ->requiredWithout('phone_number')
                                    ->label('Alamat Email (Opsional jika ada Telp)')
                                    ->maxLength(255),
                                TextInput::make('phone_number')
                                    ->tel()
                                    ->requiredWithout('email')
                                    ->label('Nomor Telepon/WA (Opsional jika ada Email)')
                                    ->maxLength(15),
                                Select::make('gender')
                                    ->options([
                                        'P' => 'Perempuan',
                                        'L' => 'Laki-laki',
                                    ])
                                    ->default('P')
                                    ->required()
                                    ->native(false)
                                    ->selectablePlaceholder(false)
                                    ->label('Jenis Kelamin'),
                            ])
                            ->createOptionUsing(function (array $data) {
                                // Default password & role
                                $data['password'] = bcrypt('12345678'); 
                                $data['role'] = 'penyewa';
                                if (empty($data['email'])) {
                                    $data['email'] = ($data['phone_number'] ?? uniqid()) . '@no-email.com';
                                }
                                return \App\Models\User::create($data)->id;
                            }),

                        Select::make('room_type_id')
                            ->relationship('roomType', 'name')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('room_id', null);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->rule(static function ($get, ?\App\Models\Reservation $record) {
                                return static function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $typeId = $get('room_type_id');
                                    $checkIn = $get('check_in_date');
                                    $checkOut = $get('check_out_date');

                                    if ($typeId && $checkIn && $checkOut) {
                                        $roomType = \App\Models\RoomType::find($typeId);
                                        if (!$roomType) return;
                                        
                                        $totalRooms = \App\Models\Room::where('room_type_id', $typeId)
                                            ->where('status', '!=', 'maintenance')
                                            ->count();
                                        
                                        $overlappingReservations = \App\Models\Reservation::where('room_type_id', $typeId)
                                            ->whereIn('status', ['pending', 'confirmed', 'active'])
                                            ->where('check_in_date', '<', $checkOut)
                                            ->where('check_out_date', '>', $checkIn)
                                            ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                            ->count();
                                            
                                        if ($overlappingReservations >= $totalRooms) {
                                            $fail("Tidak ada kamar bertipe {$roomType->name} yang tersedia secara penuh pada rentang tanggal tersebut.");
                                        }
                                    }
                                };
                            })
                            ->label('Tipe Kamar'),

                        // 2. INPUT KONTRAK WAKTU
                        DatePicker::make('check_in_date')
                            ->required()
                            ->minDate(today())
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $set('room_id', null);
                                if ($state) {
                                    $checkIn = \Carbon\Carbon::parse($state);
                                    $checkOut = $get('check_out_date') ? \Carbon\Carbon::parse($get('check_out_date')) : null;
                                    if (!$checkOut || $checkOut->lte($checkIn)) {
                                        $set('check_out_date', $checkIn->copy()->addDay()->toDateString());
                                    }
                                }
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Rencana Check-In'),

                        DatePicker::make('check_out_date')
                            ->required()
                            ->minDate(fn ($get) => $get('check_in_date') ? \Carbon\Carbon::parse($get('check_in_date'))->addDay() : today()->addDay())
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($set, $get) {
                                $set('room_id', null);
                                self::calculateTotalPrice($set, $get);
                            })
                            ->label('Rencana Check-Out'),

                        // 3. NOMOR KAMAR (Disembunyikan pada saat create)
                        Select::make('room_id')
                            ->nullable()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->placeholder(fn ($get) => (!$get('check_in_date') || !$get('check_out_date') || !$get('room_type_id')) ? 'Pilih Tipe & Tanggal Dulu' : 'Pilih Kamar Tersedia')
                            ->label('Nomor Kamar')
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->relationship(
                                name: 'room',
                                titleAttribute: 'room_number',
                                modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, callable $get, ?\App\Models\Reservation $record) {
                                    $typeId = $get('room_type_id');
                                    $checkIn = $get('check_in_date');
                                    $checkOut = $get('check_out_date');
                                    
                                    // Jika form belum diisi lengkap, query kosongkan saja (kecuali saat edit, pastikan kamar saat ini tetap ter-load)
                                    if (!$typeId || !$checkIn || !$checkOut) {
                                        if ($record && $record->room_id) {
                                            return $query->where('id', $record->room_id);
                                        }
                                        return $query->whereRaw('1 = 0'); 
                                    }
                                    
                                    $query->where('room_type_id', $typeId)
                                          ->where('status', '!=', 'maintenance');
                                          
                                    $query->whereNotIn('id', function ($subQuery) use ($checkIn, $checkOut, $record) {
                                        $subQuery->select('room_id')
                                            ->from('reservations')
                                            ->whereNotNull('room_id')
                                            ->whereIn('status', ['pending', 'confirmed', 'active'])
                                            ->where('check_in_date', '<', $checkOut)
                                            ->where('check_out_date', '>', $checkIn);
                                            
                                        // PENTING: Kecualikan reservasi yang SEDANG DIEDIT ini dari pengecekan bentrok
                                        if ($record) {
                                            $subQuery->where('id', '!=', $record->id);
                                        }
                                    });
                                    
                                    // PENTING: Pastikan kamar yang saat ini sudah terpilih (saved in DB) SELALU disertakan dalam opsi
                                    // meskipun mungkin status kamar itu sedang ada kondisi lain, agar label '101' bisa di-render oleh Filament
                                    if ($record && $record->room_id) {
                                        $query->orWhere('id', $record->room_id);
                                    }
                                    
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

                    ])->columns(2),
                    

                ])->columnSpan(['lg' => 2]),

                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Siklus Operasional')
                        ->schema([
                            Select::make('status')
                                ->required()
                                ->options([
                                    'pending' => 'Pending',
                                    'confirmed' => 'Confirmed',
                                    'active' => 'Active',
                                    'completed' => 'Completed',
                                    'refunding' => 'Refunding',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->default('pending')
                                ->label('Status Reservasi')
                                ->hidden(fn (string $operation) => $operation === 'create')
                                ->disabled()
                                ->dehydrated(),

                            Select::make('payment_method')
                                ->options([
                                    'transfer' => 'Transfer Bank',
                                    'cash' => 'Tunai',
                                ])
                                ->required()
                                ->native(false)
                                ->searchable()
                                ->default('transfer')
                                ->label('Metode Pembayaran')
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(),

                            Textarea::make('notes')
                                ->nullable()
                                ->label('Catatan Lapangan'),
                        ]),
                    
                    \Filament\Schemas\Components\Section::make('Informasi Data')
                        ->schema([
                            \Filament\Forms\Components\Placeholder::make('created_at')
                                ->label('Dibuat pada')
                                ->content(fn ($record) => $record ? $record->created_at->diffForHumans() : '-'),
                                
                            \Filament\Forms\Components\Placeholder::make('updated_at')
                                ->label('Terakhir diubah')
                                ->content(fn ($record) => $record ? $record->updated_at->diffForHumans() : '-'),
                        ])->hidden(fn (string $operation) => $operation === 'create'),
                ])->columnSpan(['lg' => 1]),

                // 6. DETAIL PEMBAYARAN (Bisa Diedit Langsung di Sini)
                \Filament\Forms\Components\Repeater::make('payments')
                    ->relationship('payments')
                    ->label('Detail Tagihan & Pembayaran')
                    ->addable(false) // Tidak bisa tambah tagihan manual dari sini (karena sudah otomatis terbuat)
                    ->deletable(false) // Tidak bisa dihapus untuk menjaga riwayat
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('payment_code')
                            ->label('Kode Tiket Pembayaran')
                            ->disabled()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\TextInput::make('payment_date')
                            ->label('Tanggal Pembayaran Terakhir')
                            ->disabled(), // Tetap tidak bisa diedit manual sesuai permintaan

                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->label('Status Validasi Pembayaran')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        \Filament\Forms\Components\Select::make('bank_account_id')
                            ->relationship('bankAccount', 'bank_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->bank_name . ' - ' . $record->account_number . ' a.n ' . $record->account_name)
                            ->label('Rekening Tujuan (Dipilih oleh Penyewa)')
                            ->disabled()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Placeholder::make('payment_proof_preview')
                            ->label('Bukti Transfer yang Diunggah')
                            ->content(function ($record) {
                                if ($record && $record->payment_proof) {
                                    $url = asset('storage/' . $record->payment_proof);
                                    return new \Illuminate\Support\HtmlString("
                                        <div style='margin-top: 10px;'>
                                            <a href='{$url}' target='_blank'>
                                                <img src='{$url}' style='max-width: 100%; max-height: 350px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;' alt='Bukti Transfer'/>
                                            </a>
                                            <p style='margin-top: 8px; font-size: 0.85rem; color: #64748b;'>Klik gambar untuk memperbesar.</p>
                                        </div>
                                    ");
                                }
                                return new \Illuminate\Support\HtmlString("<span style='color: #94a3b8; font-style: italic;'>Belum ada bukti transfer yang diunggah.</span>");
                            })
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
                                    return !in_array($get('status'), ['pending', 'paid']);
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
                                    return !in_array($get('status'), ['pending', 'paid']);
                                }),
                        ])->columnSpanFull(),
                    ])
                    ->columns(2) // Agar 2 baris 2 kolom (Tanggal & Status di baris 1, Bank & Rekening di baris 2)
                    ->columnSpanFull()
                    ->hidden(fn (string $operation) => $operation === 'create'), // Pastikan murni tersembunyi di halaman Create
            ])->columns(3);
    }
}
