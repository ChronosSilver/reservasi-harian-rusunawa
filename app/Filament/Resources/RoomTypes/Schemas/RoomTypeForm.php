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
                \Filament\Schemas\Components\Section::make('Informasi Tipe Kamar')
                    ->description('Kelola detail tipe kamar dan harga dasarnya.')
                    ->schema([
                        Select::make('name')
                            ->label('Tipe Kamar')
                            ->options(['AC' => 'AC', 'Kipas' => 'Kipas'])
                            ->default('AC')
                            ->required(),
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
            ]);
    }
}
