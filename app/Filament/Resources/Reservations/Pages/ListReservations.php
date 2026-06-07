<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

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
            
            'Pending' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending'))
                ->badge(\App\Models\Reservation::query()->where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'Confirmed' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'confirmed'))
                ->badge(\App\Models\Reservation::query()->where('status', 'confirmed')->count())
                ->badgeColor('success'),
                
            'Active' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'active'))
                ->badge(\App\Models\Reservation::query()->where('status', 'active')->count())
                ->badgeColor('info'),
                
            'Completed' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'completed'))
                ->badge(\App\Models\Reservation::query()->where('status', 'completed')->count())
                ->badgeColor('gray'),
                
            'Refunding' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'refunding'))
                ->badge(\App\Models\Reservation::query()->where('status', 'refunding')->count())
                ->badgeColor('warning'),
                
            'Cancelled' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'cancelled'))
                ->badge(\App\Models\Reservation::query()->where('status', 'cancelled')->count())
                ->badgeColor('danger'),
        ];
    }
}
