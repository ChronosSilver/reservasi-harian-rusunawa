<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\Payments\Widgets\PaymentStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => \Filament\Schemas\Components\Tabs\Tab::make(),
            
            'Pending' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'pending'))
                ->badge(\App\Models\Payment::query()->where('status', 'pending')->count())
                ->badgeColor('warning'),
                
            'Paid' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'paid'))
                ->badge(\App\Models\Payment::query()->where('status', 'paid')->count())
                ->badgeColor('info'),
                
            'Verified' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'verified'))
                ->badge(\App\Models\Payment::query()->where('status', 'verified')->count())
                ->badgeColor('success'),
                
            'Refunded' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'refunded'))
                ->badge(\App\Models\Payment::query()->where('status', 'refunded')->count())
                ->badgeColor('warning'),
                
            'Rejected' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('status', 'rejected'))
                ->badge(\App\Models\Payment::query()->where('status', 'rejected')->count())
                ->badgeColor('danger'),
        ];
    }
}
