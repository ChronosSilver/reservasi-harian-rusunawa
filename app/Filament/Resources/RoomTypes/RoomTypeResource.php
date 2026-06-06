<?php

namespace App\Filament\Resources\RoomTypes;

use App\Filament\Resources\RoomTypes\Pages\CreateRoomType;
use App\Filament\Resources\RoomTypes\Pages\EditRoomType;
use App\Filament\Resources\RoomTypes\Pages\ListRoomTypes;
use App\Filament\Resources\RoomTypes\Schemas\RoomTypeForm;
use App\Filament\Resources\RoomTypes\Tables\RoomTypesTable;
use App\Models\RoomType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class RoomTypeResource extends Resource
{
    protected static ?string $model = RoomType::class;

    protected static ?string $modelLabel = 'Tipe Kamar';
    protected static ?string $pluralModelLabel = 'Tipe Kamar';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;
    protected static ?string $navigationLabel = 'Tipe Kamar';
    protected static string|\UnitEnum|null $navigationGroup = 'Fasilitas Rusun';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoomTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tipe Kamar')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('extra_person_fee')
                    ->label('Biaya Ekstra')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                // Biarkan kosong atau biarkan isi bawaannya jika ada
            ])
            ->actions([
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
            RelationManagers\RoomsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomTypes::route('/'),
            'create' => CreateRoomType::route('/create'),
            'edit' => EditRoomType::route('/{record}/edit'),
        ];
    }
}
