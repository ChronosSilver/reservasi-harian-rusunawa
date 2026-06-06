<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected static function booted()
    {
        // Generate kode tiket otomatis saat reservasi baru dibuat
        static::creating(function (Reservation $reservation) {
            $date = now()->format('ymd');
            $random = strtoupper(Str::random(4));
            $reservation->ticket_code = 'TICK-' . $date . '-' . $random;
        });

        // Otomatisasi: Setiap kali Reservasi baru dibuat (baik oleh Admin maupun User nanti),
        // sistem akan otomatis men-generate data Pembayaran berstatus 'pending'.
        static::created(function (Reservation $reservation) {
            $reservation->payments()->create([
                'amount' => $reservation->total_price,
                'status' => 'pending',
                'payment_date' => null,
            ]);
        });
    }

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'ticket_code',
        'user_id',
        'room_type_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'guest_count',
        'total_price',
        'payment_method',
        'status',
        'notes',
    ];

    // Hubungkan reservasi ke manusia yang memesan
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Hubungkan reservasi ke tipe kamar yang dipilih
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    // Hubungkan reservasi ke nomor kamar fisik (nullable)
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}