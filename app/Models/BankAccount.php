<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'is_active',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
