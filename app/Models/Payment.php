<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    // 1. Atribut yang diizinkan untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'reservation_id',
        'amount',
        'payment_method',
        'payment_proof',
        'status',
        'payment_date',
    ];

    // 2. Relasi Mutlak: Setiap baris pembayaran selalu dimiliki oleh satu Reservasi
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}