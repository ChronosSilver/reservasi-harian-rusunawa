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

                        \Filament\Forms\Components\TextInput::make('payment_code')
                            ->label('Kode Pembayaran')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Select::make('bank_account_id')
                            ->relationship('bankAccount', 'bank_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->bank_name . ' - ' . $record->account_number . ' a.n ' . $record->account_name)
                            ->label('Rekening Tujuan (Dipilih oleh Penyewa)')
                            ->disabled()
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
                                if (!$record || !$record->payment_proof) return new \Illuminate\Support\HtmlString("<span style='color: #94a3b8; font-style: italic;'>Tidak ada foto bukti transfer.</span>");
                                $url = asset('storage/' . $record->payment_proof);
                                return new \Illuminate\Support\HtmlString("
                                    <div style='margin-top: 10px;'>
                                        <a href='{$url}' target='_blank'>
                                            <img src='{$url}' style='max-width: 100%; max-height: 350px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;' alt='Bukti Transfer'/>
                                        </a>
                                        <p style='margin-top: 8px; font-size: 0.85rem; color: #64748b;'>Klik gambar untuk melihat ukuran penuh di tab baru.</p>
                                    </div>
                                ");
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