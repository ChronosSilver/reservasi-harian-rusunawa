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
                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Informasi Kamar')
                        ->description('Kelola detail kamar rusunawa.')
                        ->schema([
                            TextInput::make('room_number')
                                ->label('Nomor Kamar')
                                ->required()
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(),
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
                                ->native(false)
                                ->selectablePlaceholder(false)
                                ->required()
                                ->disabled(fn (string $operation) => $operation === 'edit')
                                ->dehydrated(),
                            TextInput::make('capacity')
                                ->label('Kapasitas')
                                ->required()
                                ->numeric()
                                ->default(3),
                        ])
                        ->columns(2),
                ])->columnSpan(['lg' => 2]),

                \Filament\Schemas\Components\Group::make()->schema([
                    \Filament\Schemas\Components\Section::make('Siklus Operasional')
                        ->schema([
                            Select::make('status')
                                ->label('Status Kamar')
                                ->options([
                                    'available' => 'Available',
                                    'occupied' => 'Occupied',
                                    'cleaning' => 'Cleaning',
                                    'maintenance' => 'Maintenance',
                                ])
                                ->default('available')
                                ->required()
                                ->disabled()
                                ->dehydrated(),
                        ])->hidden(fn (string $operation) => $operation === 'create'),
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
