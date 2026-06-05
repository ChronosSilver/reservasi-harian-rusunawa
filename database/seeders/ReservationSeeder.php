<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RoomType;
use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'penyewa@student.untan.ac.id')->first();

        $tipeAC = RoomType::where('name', 'AC')->first();
        $tipeKipas = RoomType::where('name', 'Kipas')->first();

        Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeKipas->id,
            'check_in_date' => Carbon::now()->addDays(5),
            'check_out_date' => Carbon::now()->addMonths(6),
            'guest_count' => 1,
            'total_price' => $tipeKipas->base_price * 6,
            'status' => 'pending',
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeAC->id,
            'check_in_date' => Carbon::now()->addDays(2),
            'check_out_date' => Carbon::now()->addMonths(6),
            'guest_count' => 2,
            'total_price' => ($tipeAC->base_price + $tipeAC->extra_person_fee) * 6,
            'status' => 'confirmed',
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeKipas->id,
            'check_in_date' => Carbon::now()->subDays(10), 
            'check_out_date' => Carbon::now()->addMonths(6),
            'guest_count' => 1,
            'total_price' => $tipeKipas->base_price * 6,
            'status' => 'confirmed',
        ]);
    }
}