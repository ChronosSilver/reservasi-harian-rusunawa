<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
class Room extends Model
{
    use HasFactory;

    // Daftarkan kolom yang boleh diisi secara sistem
    protected $fillable = [
        'room_type_id',
        'room_number',
        'building',
        'status',
    ];
    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}