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
use Filament\Actions\DeleteBulkAction;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Reservasi';

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('user.name')
                ->label('Nama Penyewa')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('roomType.name')
                ->label('Tipe')
                ->sortable(),

            Tables\Columns\TextColumn::make('room.room_number')
                ->label('Kamar')
                ->placeholder('Belum Diplot') // Menangani kamar yang belum diberikan ke penyewa
                ->sortable(),

            // 3. Kontrak Waktu (Format Manusia)
            Tables\Columns\TextColumn::make('check_in_date')
                ->label('Check-In')
                ->date('d M Y') // Output: 10 Aug 2026
                ->sortable(),

            Tables\Columns\TextColumn::make('check_out_date')
                ->label('Check-Out')
                ->date('d M Y')
                ->sortable(),

            // 4. Finansial
            Tables\Columns\TextColumn::make('total_price')
                ->label('Tagihan')
                ->money('IDR', locale: 'id') // Output: Rp 150.000,00
                ->sortable(),

            // 5. Audit Status Visual
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge() // Mengubah teks menjadi kapsul warna
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'active' => 'info',
                    'completed' => 'gray',
                    'cancelled' => 'danger',
                })
                ->sortable(),
        ])
        ->filters([
            // Filter cepat berdasarkan status di kanan atas tabel
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending (Menunggu Bayar)',
                    'confirmed' => 'Confirmed (Lunas)',
                    'active' => 'Active (Sedang Huni)',
                    'completed' => 'Completed (Selesai)',
                    'cancelled' => 'Cancelled (Batal)',
                ]),
        ])
        ->actions([
            Action::make('processCheckIn')
                ->label('Check-In Riil')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Proses Check-In Mahasiswa')
                ->modalDescription('Apakah mahasiswa sudah berada di lokasi dan menerima kunci fisik?')
                // ATURAN VISIBILITAS: Hanya muncul jika status sudah LUNAS (confirmed) dan BELUM pernah check-in riil
                ->visible(fn ($record) => $record->status === 'confirmed' && $record->actual_check_in === null)
                ->action(function ($record) {
                    $record->update([
                        'actual_check_in' => now(), // Otomatis merekam detik ini dari server
                        'status' => 'active',       // Status otomatis berubah jadi "Sedang Dihuni"
                    ]);
                }),

            Action::make('processCheckOut')
                ->label('Check-Out Riil')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Proses Check-Out & Pengembalian Kunci')
                ->modalDescription('Pastikan kamar telah diperiksa dan tidak ada kerusakan fasilitas.')
                // ATURAN VISIBILITAS: Hanya muncul jika status sedang AKTIF dihuni dan BELUM pernah check-out riil
                ->visible(fn ($record) => $record->status === 'active' && $record->actual_check_out === null)
                ->action(function ($record) {
                    $record->update([
                        'actual_check_out' => now(), // Otomatis merekam detik ini dari server
                        'status' => 'completed',     // Status otomatis berubah jadi "Selesai"
                    ]);
                }),
            EditAction::make(),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
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
