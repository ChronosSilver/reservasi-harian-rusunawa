<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    public function getHeading(): string
    {
        return 'Detail & Edit Kamar';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('set_available')
                ->label('Tersedia')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->disabled(fn (\App\Models\Room $record) => $record->status !== 'maintenance')
                ->action(function (\App\Models\Room $record, \Filament\Resources\Pages\EditRecord $livewire) {
                    $record->update(['status' => 'available']);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Tersedia')
                        ->body('Kamar sekarang berstatus Available.')
                        ->send();
                        
                    $livewire->refreshFormData(['status']);
                }),

            \Filament\Actions\Action::make('set_maintenance')
                ->label('Sedang Diperbaiki')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->disabled(fn (\App\Models\Room $record) => $record->status === 'maintenance')
                ->action(function (\App\Models\Room $record, \Filament\Resources\Pages\EditRecord $livewire) {
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
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Pemeliharaan')
                        ->body('Kamar berhasil masuk ke mode Maintenance.')
                        ->send();
                        
                    $livewire->refreshFormData(['status']);
                }),

            DeleteAction::make(),
        ];
    }
}
