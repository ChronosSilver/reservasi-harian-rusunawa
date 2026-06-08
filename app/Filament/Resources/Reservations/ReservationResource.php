<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\Resources\Reservations\Tables\ReservationsTable;
use App\Models\Reservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $modelLabel = 'Reservasi';
    protected static ?string $pluralModelLabel = 'Reservasi';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'Reservasi';
    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi & Sewa';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultPaginationPageOption(25)
            ->columns([
            Tables\Columns\TextColumn::make('ticket_code')
                ->label('Kode Tiket')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Kode Tiket disalin')
                ->weight('bold')
                ->color('primary')
                ->toggleable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Nama Penyewa')
                ->searchable()
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('roomType.name')
                ->label('Tipe Kamar')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('room.room_number')
                ->label('Kamar')
                ->placeholder('Belum Diplot') // Menangani kamar yang belum diberikan ke penyewa
                ->sortable()
                ->toggleable(),

            // 3. Kontrak Waktu (Format Manusia)
            Tables\Columns\TextColumn::make('check_in_date')
                ->label('Tanggal Check-In')
                ->date('d M Y') // Output: 10 Aug 2026
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('check_out_date')
                ->label('Tanggal Check-Out')
                ->date('d M Y')
                ->sortable()
                ->toggleable(),

            // 4. Finansial
            Tables\Columns\TextColumn::make('total_price')
                ->label('Total Tagihan')
                ->money('IDR', locale: 'id') // Output: Rp 150.000,00
                ->sortable()
                ->toggleable(),

            // 5. Audit Status Visual
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge() // Mengubah teks menjadi kapsul warna
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'active' => 'info',
                    'completed' => 'gray',
                    'refunding' => 'warning',
                    'cancelled' => 'danger',
                })
                ->sortable()
                ->toggleable(),
                
            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat Pada')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Filter cepat berdasarkan status di kanan atas tabel
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
        ])
        ->recordActionsColumnLabel('')
        ->actions([
            ActionGroup::make([
                Action::make('processCheckIn')
                    ->label('Check-In Riil')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Check-In Mahasiswa')
                    ->modalDescription('Apakah mahasiswa sudah berada di lokasi dan menerima kunci fisik?')
                    // ATURAN VISIBILITAS: Redup jika status BUKAN LUNAS (confirmed) ATAU SUDAH pernah check-in riil ATAU waktu kurang dari jam 14:00 di hari-H ATAU kamar belum dipilih
                    ->disabled(fn ($record) => 
                        $record->status !== 'confirmed' || 
                        $record->actual_check_in !== null || 
                        $record->room_id === null ||
                        now()->timezone('Asia/Jakarta')->lt(\Carbon\Carbon::parse($record->check_in_date)->setTimezone('Asia/Jakarta')->setTime(14, 0))
                    )
                    ->action(function ($record) {
                        $record->update([
                            'actual_check_in' => now(), // Otomatis merekam detik ini dari server
                            'status' => 'active',       // Status otomatis berubah jadi "Sedang Dihuni"
                        ]);
                        // Mengubah status kamar menjadi occupied
                        if ($record->room) {
                            $record->room->update(['status' => 'occupied']);
                        }
                    }),

                Action::make('processCheckOut')
                    ->label('Check-Out Riil')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Check-Out & Pengembalian Kunci')
                    ->modalDescription('Pastikan kamar telah diperiksa dan tidak ada kerusakan fasilitas.')
                    // ATURAN VISIBILITAS: Redup jika status BUKAN AKTIF dihuni ATAU SUDAH pernah check-out riil (bisa check-out lebih awal)
                    ->disabled(fn ($record) => $record->status !== 'active' || $record->actual_check_out !== null)
                    ->action(function ($record) {
                        $record->update([
                            'actual_check_out' => now(), // Otomatis merekam detik ini dari server
                            'status' => 'completed',     // Status otomatis berubah jadi "Selesai"
                        ]);
                        // Mengubah status kamar menjadi cleaning dan memanggil Job 1 jam kemudian
                        if ($record->room) {
                            $record->room->update(['status' => 'cleaning']);
                            \App\Jobs\UpdateRoomStatusToAvailable::dispatch($record->room_id)->delay(now()->addMinute());
                        }
                    }),
                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Batalkan Reservasi')
                    ->modalDescription('Apakah Anda yakin ingin membatalkan reservasi ini?')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                    ->form([
                        \Filament\Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Alasan Pembatalan')
                            ->placeholder('Masukkan alasan pembatalan...')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                        if ($record->status === 'pending') {
                            $record->update(['status' => 'cancelled']);
                            $record->payments()->update([
                                'status' => 'rejected',
                                'cancellation_reason' => $data['cancellation_reason']
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Dibatalkan')
                                ->body('Reservasi Pending berhasil dibatalkan.')
                                ->send();
                        } elseif ($record->status === 'confirmed') {
                            $record->update(['status' => 'refunding']);
                            $record->payments()->update(['cancellation_reason' => $data['cancellation_reason']]);
                            if ($record->room) {
                                $record->room->update(['status' => 'available']);
                            }
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('Masuk Antrean Refund')
                                ->body('Reservasi Confirmed dibatalkan. Menunggu proses Refund.')
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->label('Ubah'),
            ]),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                // DeleteBulkAction::make(), // Dinonaktifkan untuk menjaga riwayat transaksi
            ]),
        ]);
}

    public static function getWidgets(): array
    {
        return [
            Widgets\ReservationStats::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit' => EditReservation::route('/{record}/edit'),
        ];
    }
}
