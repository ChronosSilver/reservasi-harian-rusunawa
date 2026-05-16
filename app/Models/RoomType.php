<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class RoomType extends Model
{
    use HasFactory;
    // 1. Membuka gembok agar Seeder/Formulir bisa memasukkan data ke kolom ini
    protected $fillable = [
        'name', 
        'base_price',
        'extra_person_fee'
    ];
    // 2. Deklarasi Relasi: Satu Tipe Kamar memiliki BANYAK Kamar
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}