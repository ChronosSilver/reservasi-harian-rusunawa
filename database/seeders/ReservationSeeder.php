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

        // Reservasi 1: Pending (Kipas, 1 orang, 3 hari)
        $res1 = Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeKipas->id,
            'check_in_date' => Carbon::now(),
            'check_out_date' => Carbon::now()->addDays(3),
            'guest_count' => 1,
            'total_price' => $tipeKipas->base_price * 3,
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);
        // Pembayaran otomatis terbuat dengan status 'pending' (dari Model Event)

        // Reservasi 2: Confirmed (AC, 3 orang (kena extra fee!), 5 hari)
        $res2 = Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeAC->id,
            'check_in_date' => Carbon::now()->addDays(5),
            'check_out_date' => Carbon::now()->addDays(10),
            'guest_count' => 3, // Karena > 2, dikenakan extra fee
            'total_price' => ($tipeAC->base_price + $tipeAC->extra_person_fee) * 5,
            'payment_method' => 'transfer',
            'status' => 'confirmed',
        ]);
        // Modifikasi pembayaran otomatisnya menjadi Lunas
        /** @var \App\Models\Payment|null $payment2 */
        $payment2 = $res2->payments()->first();
        if ($payment2) {
            $payment2->update([
                'status' => 'verified',
                'payment_date' => Carbon::now(),
            ]);
        }

        // Reservasi 3: Active (Kipas, 2 orang, 4 hari)
        $res3 = Reservation::create([
            'user_id' => $user->id,
            'room_type_id' => $tipeKipas->id,
            'check_in_date' => Carbon::now()->subDays(2), 
            'check_out_date' => Carbon::now()->addDays(2),
            'guest_count' => 2,
            'total_price' => $tipeKipas->base_price * 4,
            'payment_method' => 'cash',
            'status' => 'active',
        ]);
        // Modifikasi pembayaran otomatisnya menjadi Lunas
        /** @var \App\Models\Payment|null $payment3 */
        $payment3 = $res3->payments()->first();
        if ($payment3) {
            $payment3->update([
                'status' => 'verified',
                'payment_date' => Carbon::now()->subDays(2),
            ]);
        }
    }
}