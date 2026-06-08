<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BankAccount::create([
            'bank_name' => 'Bank Mandiri',
            'account_name' => 'Rusunawa Untan',
            'account_number' => '1460001234567',
            'is_active' => true,
        ]);

        BankAccount::create([
            'bank_name' => 'Bank BCA',
            'account_name' => 'Rusunawa Untan',
            'account_number' => '0211234567',
            'is_active' => true,
        ]);
        
        BankAccount::create([
            'bank_name' => 'Bank BNI',
            'account_name' => 'Rusunawa Untan',
            'account_number' => '0098765432',
            'is_active' => false,
        ]);
    }
}
