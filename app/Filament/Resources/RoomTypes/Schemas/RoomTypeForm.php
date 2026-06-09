<?php

namespace App\Filament\Resources\RoomTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoomTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Informasi Tipe Kamar')
                        ->description('Kelola detail tipe kamar dan harga dasarnya.')
                        ->schema([
                            Select::make('name')
                                ->label('Tipe Kamar')
                                ->options(['AC' => 'AC', 'Kipas' => 'Kipas'])
                                ->default('AC')
                                ->native(false)
                                ->selectablePlaceholder(false)
                                ->required()
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(),
                            TextInput::make('base_price')
                                ->label('Harga Dasar')
                                ->required()
                                ->numeric()
                                ->prefix('Rp'),
                            TextInput::make('extra_person_fee')
                                ->label('Biaya Ekstra')
                                ->required()
                                ->numeric()
                                ->default(25000.0),
                        ])
                        ->columns(2),
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
