<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;


class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('room_number')
                    ->label('Nomor Kamar')
                    ->required(),
                Select::make('room_type_id')
                    ->label('Tipe Kamar')
                    ->relationship('roomType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('building')
                    ->label('Gedung')
                    ->options([
                        'Rusunawa Putri' => 'Rusunawa Putri',
                        'Rusun Inn' => 'Rusun Inn',
                        'Rusunawa Putra' => 'Rusunawa Putra',
                    ])
                    ->default('Rusunawa Putri')
                    ->required(),
                TextInput::make('capacity')
                    ->label('Kapasitas')
                    ->required()
                    ->numeric()
                    ->default(3),
                Select::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'cleaning' => 'Cleaning',
                        'maintenance' => 'Maintenance',
                    ])
                    ->default('available')
                    ->required(),
            ]);
    }
}
