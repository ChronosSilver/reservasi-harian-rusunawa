<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $modelLabel = 'Kamar';
    protected static ?string $pluralModelLabel = 'Kamar';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;
    protected static ?string $navigationLabel = 'Kamar';
    protected static string|\UnitEnum|null $navigationGroup = 'Fasilitas Rusun';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'room_number';

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('room_number')
                    ->label('Nomor Kamar')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('building')
                    ->label('Gedung')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roomType.name')
                    ->label('Tipe Kamar')
                    ->badge() // Tambahan opsional: Membuat tampilannya seperti label warna-warni agar lebih elegan
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status Kamar')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',   // Hijau
                        'occupied' => 'info',       // Biru
                        'cleaning' => 'warning',    // Kuning/Jingga
                        'maintenance' => 'danger',   // Merah
                        default => 'gray',
                    })
                    ->searchable(),
            ])
            ->filters([
            ])
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('set_available')
                        ->label('Tersedia')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->disabled(fn (Room $record) => $record->status !== 'maintenance')
                        ->action(function (Room $record) {
                            $record->update(['status' => 'available']);
                        }),
                        
                    \Filament\Actions\Action::make('set_maintenance')
                        ->label('Sedang Diperbaiki')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->disabled(fn (Room $record) => $record->status === 'maintenance')
                        ->action(function (Room $record) {
                            $activeReservationsCount = $record->reservations()
                                ->whereIn('status', ['pending', 'confirmed', 'active'])
                                ->count();
                            
                            if ($activeReservationsCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->warning()
                                    ->title('Kamar Terikat Reservasi!')
                                    ->body("Terdapat {$activeReservationsCount} reservasi terkait dengan kamar ini. Harap ubah nomor kamar untuk reservasi belum check-in, atau pindahkan penyewa yang sudah check-in.")
                                    ->send();
                                return;
                            }
                            
                            $record->update(['status' => 'maintenance']);
                        }),

                    EditAction::make()
                        ->label('Ubah'),
                ]),
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
            \App\Filament\Resources\Rooms\RelationManagers\ReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\Rooms\Widgets\RoomResourceStats::class,
        ];
    }
}
