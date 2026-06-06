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

        $rooms = [];
        $floors = [1, 2, 3];
        foreach ($floors as $floor) {
            for ($i = 1; $i <= 10; $i++) {
                $numStr = sprintf('%02d', $i);
                $roomNumber = $floor . $numStr;
                $isOdd = $i % 2 !== 0;
                $rooms[] = [
                    'room_number' => $roomNumber,
                    'room_type_id' => $isOdd ? $tipeAC->id : $tipeKipas->id,
                    'status' => 'available',
                    'capacity' => 3
                ];
            }
        }

        // 3. Eksekusi data ke pangkalan data
        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number']], // Kunci pencarian agar tidak ganda
                [
                    'room_type_id' => $room['room_type_id'],
                    'building' => 'Rusunawa Putri', // Gedung diseragamkan secara mutlak
                    'capacity' => $room['capacity'],
                    'status' => $room['status'],
                ]
            );
        }
    }
}