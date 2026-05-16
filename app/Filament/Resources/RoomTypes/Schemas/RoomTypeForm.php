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
            ]);
    }
}
