<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Rooms\Widgets\RoomResourceStats::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => \Filament\Schemas\Components\Tabs\Tab::make(),
            
            'Available' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'available'))
                ->badge(\App\Models\Room::query()->where('status', 'available')->count())
                ->badgeColor('success'),
                
            'Occupied' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'occupied'))
                ->badge(\App\Models\Room::query()->where('status', 'occupied')->count())
                ->badgeColor('danger'),
                
            'Maintenance' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'maintenance'))
                ->badge(\App\Models\Room::query()->where('status', 'maintenance')->count())
                ->badgeColor('warning'),
        ];
    }
}
