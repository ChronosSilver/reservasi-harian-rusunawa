<?php

namespace App\Filament\Resources\Reservations\Pages;

use App\Filament\Resources\Reservations\ReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReservation extends EditRecord
{
    protected static string $resource = ReservationResource::class;

    public function getHeading(): string
    {
        return 'Detail & Edit Reservasi';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('processCheckIn')
                ->label('Check-In Riil')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Proses Check-In Mahasiswa')
                ->modalDescription('Apakah mahasiswa sudah berada di lokasi dan menerima kunci fisik?')
                ->disabled(fn ($record) => $record->status !== 'confirmed' || $record->actual_check_in !== null || $record->room_id === null || now()->timezone('Asia/Jakarta')->lt(\Carbon\Carbon::parse($record->check_in_date)->setTimezone('Asia/Jakarta')->setTime(14, 0)))
                ->action(function ($record) {
                    $record->update([
                        'actual_check_in' => now(),
                        'status' => 'active',
                    ]);
                    if ($record->room) {
                        $record->room->update(['status' => 'occupied']);
                    }
                    return redirect(request()->header('Referer'));
                }),

            \Filament\Actions\Action::make('processCheckOut')
                ->label('Check-Out Riil')
                ->icon('heroicon-o-arrow-left-on-rectangle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Proses Check-Out & Pengembalian Kunci')
                ->modalDescription('Pastikan kamar telah diperiksa dan tidak ada kerusakan fasilitas.')
                ->disabled(fn ($record) => $record->status !== 'active' || $record->actual_check_out !== null)
                ->action(function ($record) {
                    $record->update([
                        'actual_check_out' => now(),
                        'status' => 'completed',
                    ]);
                    if ($record->room) {
                        $record->room->update(['status' => 'cleaning']);
                        \App\Jobs\UpdateRoomStatusToAvailable::dispatch($record->room_id)->delay(now()->addMinute());
                    }
                    return redirect(request()->header('Referer'));
                }),

            \Filament\Actions\Action::make('cancel')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Batalkan Reservasi')
                ->modalDescription('Apakah Anda yakin ingin membatalkan reservasi ini?')
                ->visible(fn ($record) => in_array($record->status, ['pending', 'confirmed']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->placeholder('Masukkan alasan pembatalan...')
                        ->required(),
                ])
                ->action(function (array $data, $record) {
                    if ($record->status === 'pending') {
                        $record->update(['status' => 'cancelled']);
                        $record->payments()->update([
                            'status' => 'rejected',
                            'cancellation_reason' => $data['cancellation_reason']
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Dibatalkan')
                            ->body('Reservasi Pending berhasil dibatalkan.')
                            ->send();
                    } elseif ($record->status === 'confirmed') {
                        $record->update(['status' => 'refunding']);
                        $record->payments()->update(['cancellation_reason' => $data['cancellation_reason']]);
                        if ($record->room) {
                            $record->room->update(['status' => 'available']);
                        }
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Masuk Antrean Refund')
                            ->body('Reservasi Confirmed dibatalkan. Menunggu proses Refund.')
                            ->send();
                        return redirect(request()->header('Referer'));
                    }
                }),

            \Filament\Actions\Action::make('refund')
                ->label('Refund')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->modalHeading('Proses Refund')
                ->modalDescription('Unggah bukti transfer refund untuk meresmikan pembatalan.')
                ->visible(fn ($record) => $record->status === 'refunding')
                ->modalSubmitActionLabel('Selesaikan Refund')
                ->modalCancelAction(false) // Menghilangkan tombol "Batal" bawaan yang membingungkan
                ->extraModalFooterActions(fn (\Filament\Actions\Action $action): array => [
                    \Filament\Actions\Action::make('cancel_refund')
                        ->label('Batal Refund')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Proses Refund')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan proses refund dan mengembalikan status ke Confirmed?')
                        ->action(function () use ($action) {
                            $record = $action->getRecord();
                            $record->update(['status' => 'confirmed']);
                            $record->payments()->update([
                                'status' => 'verified',
                                'cancellation_reason' => null,
                                'refund_proof' => null,
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Refund Dibatalkan')
                                ->body('Status reservasi dan pembayaran kembali normal.')
                                ->send();
                            
                            // Memaksa browser merefresh halaman secara penuh, yang otomatis akan menutup semua modal
                            $action->getLivewire()->js('window.location.reload()');
                        }),
                ])
                ->fillForm(fn ($record) => [
                    'cancellation_reason' => $record->payments()->latest()->first()?->cancellation_reason
                ])
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->disabled(),
                    \Filament\Forms\Components\FileUpload::make('refund_proof')
                        ->label('Bukti Transfer Refund')
                        ->directory('bukti-refund')
                        ->disk('public')
                        ->image()
                        ->required(fn ($record) => $record->payment_method === 'transfer'),
                ])
                ->action(function (array $data, $record) {
                    $record->update(['status' => 'cancelled']);
                    $record->payments()->update([
                        'status' => 'refunded',
                        'refund_proof' => $data['refund_proof'] ?? null,
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Refund Selesai')
                        ->body('Status reservasi menjadi cancelled dan pembayaran refunded.')
                        ->send();
                    return redirect(request()->header('Referer'));
                }),
        ];
    }
}
