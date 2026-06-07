<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    public function getHeading(): string
    {
        return 'Detail & Edit Pembayaran';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('cancel_pending')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Batalkan Reservasi')
                ->modalDescription('Apakah Anda yakin ingin membatalkan reservasi ini?')
                ->visible(fn ($record) => $record->status === 'pending')
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->required(),
                ])
                ->action(function (array $data, $record, \Filament\Actions\Action $action) {
                    $record->update([
                        'status' => 'rejected',
                        'cancellation_reason' => $data['cancellation_reason']
                    ]);
                    if ($record->reservation) {
                        $record->reservation->update(['status' => 'cancelled']);
                    }
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Dibatalkan')
                        ->body('Reservasi dan Pembayaran Pending berhasil dibatalkan.')
                        ->send();
                    $action->getLivewire()->js('window.location.reload()');
                }),

            \Filament\Actions\Action::make('cancel_paid')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => $record->status === 'paid')
                ->action(function () {
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title('Aksi Ditolak')
                        ->body('Peringatan: Anda belum memverifikasi bukti pembayaran! Silakan verifikasi terlebih dahulu.')
                        ->send();
                }),

            \Filament\Actions\Action::make('cancel_verified')
                ->label('Batalkan')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->modalHeading('Batalkan Reservasi Terverifikasi')
                ->modalDescription('Reservasi ini sudah terverifikasi. Apakah Anda yakin ingin membatalkannya?')
                ->visible(fn ($record) => $record->status === 'verified' && in_array($record->reservation->status, ['confirmed']))
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->required(),
                ])
                ->action(function (array $data, $record, \Filament\Actions\Action $action) {
                    $record->update(['cancellation_reason' => $data['cancellation_reason']]);
                    if ($record->reservation) {
                        $record->reservation->update(['status' => 'refunding']);
                        if ($record->reservation->room) {
                            $record->reservation->room->update(['status' => 'available']);
                        }
                    }
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title('Masuk Antrean Refund')
                        ->body('Reservasi dibatalkan. Menunggu proses Refund.')
                        ->send();
                    $action->getLivewire()->js('window.location.reload()');
                }),

            \Filament\Actions\Action::make('refund')
                ->label('Refund')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->modalHeading('Proses Refund')
                ->modalDescription('Unggah bukti transfer refund untuk meresmikan pembatalan.')
                ->visible(fn ($record) => $record->reservation && $record->reservation->status === 'refunding')
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
                            if ($record->reservation) {
                                $record->reservation->update(['status' => 'confirmed']);
                            }
                            $record->update([
                                'status' => 'verified',
                                'cancellation_reason' => null,
                                'refund_proof' => null,
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Refund Dibatalkan')
                                ->body('Status reservasi dan pembayaran kembali normal.')
                                ->send();
                            
                            $action->getLivewire()->js('window.location.reload()');
                        }),
                ])
                ->fillForm(fn ($record) => [
                    'cancellation_reason' => $record->cancellation_reason
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
                        ->required(fn ($record) => $record->reservation && $record->reservation->payment_method === 'transfer'),
                ])
                ->action(function (array $data, $record, \Filament\Actions\Action $action) {
                    if ($record->reservation) {
                        $record->reservation->update(['status' => 'cancelled']);
                    }
                    $record->update([
                        'status' => 'refunded',
                        'refund_proof' => $data['refund_proof'] ?? null,
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Refund Selesai')
                        ->body('Status reservasi menjadi cancelled dan pembayaran refunded.')
                        ->send();
                    $action->getLivewire()->js('window.location.reload()');
                }),
        ];
    }
}
