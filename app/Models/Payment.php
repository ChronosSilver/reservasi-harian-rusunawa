<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function (Payment $payment) {
            $date = now()->format('ymd');
            $random = strtoupper(\Illuminate\Support\Str::random(4));
            $payment->payment_code = 'INV-' . $date . '-' . $random;
        });
    }

    // 1. Atribut yang diizinkan untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'reservation_id',
        'payment_code',
        'bank_account_id',
        'amount',
        'payment_proof',
        'refund_proof',
        'cancellation_reason',
        'status',
        'payment_date',
        'verified_at',
    ];

    // 2. Relasi Mutlak: Setiap baris pembayaran selalu dimiliki oleh satu Reservasi
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}