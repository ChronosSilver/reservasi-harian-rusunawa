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

        $rooms = [
            ['room_number' => '101', 'room_type_id' => $tipeAC->id, 'status' => 'available'],
            ['room_number' => '102', 'room_type_id' => $tipeKipas->id, 'status' => 'available'],
            ['room_number' => '103', 'room_type_id' => $tipeKipas->id, 'status' => 'available'],
            ['room_number' => '104', 'room_type_id' => $tipeAC->id, 'status' => 'maintenance'],
            ['room_number' => '105', 'room_type_id' => $tipeAC->id, 'status' => 'available'],
            ['room_number' => '106', 'room_type_id' => $tipeAC->id, 'status' => 'available'],
        ];

        // 3. Eksekusi data ke pangkalan data
        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number']], // Kunci pencarian agar tidak ganda
                [
                    'room_type_id' => $room['room_type_id'],
                    'building' => 'Rusunawa Putri', // Gedung diseragamkan secara mutlak
                    'status' => $room['status'],
                ]
            );
        }
    }
}