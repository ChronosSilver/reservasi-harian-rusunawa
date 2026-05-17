<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'user_id',
        'room_type_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'guest_count',
        'total_price',
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