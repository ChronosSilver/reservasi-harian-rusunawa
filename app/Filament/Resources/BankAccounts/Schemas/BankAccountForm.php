<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bank_name')
                    ->label('Nama Bank (contoh: Mandiri, BCA)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_name')
                    ->label('Nama Pemilik Rekening')
                    ->required()
                    ->maxLength(255),
                TextInput::make('account_number')
                    ->label('Nomor Rekening')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
