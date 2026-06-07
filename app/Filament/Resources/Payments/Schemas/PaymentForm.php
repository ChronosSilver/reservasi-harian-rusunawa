<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Informasi Pembayaran')
                        ->description('Formulir pencatatan dan validasi pembayaran.')
                        ->schema([
                            // 1. Relasi ke Reservasi
                        \Filament\Forms\Components\Select::make('reservation_id')
                            ->relationship('reservation', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ($record->ticket_code ?? 'TICK-N/A') . ' - ' . $record->user->name)
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
                            ->disabled(fn (string $operation) => $operation === 'edit')
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
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->disabled()
                            ->dehydrated()
                            ->label('Status Validasi Pembayaran')
                            ->columnSpanFull(), // Dibuat 1 kolom penuh per baris

                        \Filament\Forms\Components\Hidden::make('payment_date')
                            ->default(now()),

                        \Filament\Forms\Components\TextInput::make('bank_account')
                            ->label('Nomor Rekening')
                            ->nullable()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->nullable()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\FileUpload::make('payment_proof')
                            ->directory('bukti-transfer')
                            ->disk('public')
                            ->image()
                            ->nullable()
                            ->required(fn ($get) => $get('payment_method') === 'transfer' && $get('status') === 'verified')
                            ->label('Foto Bukti Transfer (Abaikan jika tunai)')
                            ->hidden(fn (string $operation) => $operation === 'edit')
                            ->columnSpanFull(),
                            
                        \Filament\Forms\Components\Placeholder::make('payment_proof_preview')
                            ->label('Foto Bukti Transfer (Abaikan jika tunai)')
                            ->content(function ($record) {
                                if (!$record || !$record->payment_proof) return 'Tidak ada foto bukti transfer.';
                                $url = asset('storage/' . $record->payment_proof);
                                return new \Illuminate\Support\HtmlString('
                                    <div x-data="{ open: false }">
                                        <img src="'.$url.'" @click="open = true" class="h-48 rounded-lg cursor-pointer transition hover:opacity-80 border border-gray-200 dark:border-gray-700 shadow-sm" />
                                        
                                        <div x-show="open" style="display: none;" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 backdrop-blur-sm" @click="open = false">
                                            <img src="'.$url.'" class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-2xl" @click.stop />
                                            <button @click="open = false" class="absolute top-6 right-6 text-white hover:text-gray-300 bg-gray-900/50 rounded-full p-2">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                ');
                            })
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->columnSpanFull(),
                    ]),
                    
                    \Filament\Schemas\Components\Section::make('Informasi Pengembalian')
                        ->description('Data terkait proses pembatalan dan pengembalian dana.')
                        ->schema([
                            \Filament\Forms\Components\Textarea::make('cancellation_reason')
                                ->label('Alasan Pembatalan')
                                ->nullable()
                                ->disabled()
                                ->columnSpanFull(),

                            \Filament\Forms\Components\FileUpload::make('refund_proof')
                                ->label('Bukti Transfer Refund')
                                ->directory('bukti-refund')
                                ->disk('public')
                                ->image()
                                ->nullable()
                                ->disabled()
                                ->columnSpanFull(),
                        ])
                        ->hidden(fn (string $operation) => $operation === 'create'),
                ])->columnSpan(['lg' => 2]),

                \Filament\Schemas\Components\Group::make()->schema([
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
            ])->columns(3);
    }
}