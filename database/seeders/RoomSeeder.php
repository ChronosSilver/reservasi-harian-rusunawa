<?php
namespace Database\Seeders;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Pertama, kita buat beberapa tipe kamar untuk referensi
        $tipeAC = RoomType::create([
            'name' => 'AC',
            'base_price' => 150000,
            'extra_person_fee' => 25000,
        ]);

        $tipeKipas = RoomType::create([
            'name' => 'Kipas',
            'base_price' => 100000,
            'extra_person_fee' => 25000,
        ]);

        // Kemudian, kita buat beberapa kamar dengan berbagai kombinasi
        Room::create([
            'room_type_id' => $tipeAC->id,
            'room_number' => '101',
            'building' => 'Rusunawa Putri',
            'status' => 'available',
        ]);

        Room::create([
            'room_type_id' => $tipeKipas->id,
            'room_number' => '102',
            'building' => 'Rusunawa Putri',
            'status' => 'occupied', // Simulasi sudah dipesan
        ]);

        Room::create([
            'room_type_id' => $tipeAC->id,
            'room_number' => '103', // Nomor sama, gedung berbeda
            'building' => 'Rusun Inn',
            'status' => 'maintenance', // Simulasi sedang rusak
        ]);
    }
}