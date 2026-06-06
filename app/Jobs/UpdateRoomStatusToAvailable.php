<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateRoomStatusToAvailable implements ShouldQueue
{
    use Queueable;
    public $roomId;

    /**
     * Create a new job instance.
     */
    public function __construct($roomId)
    {
        $this->roomId = $roomId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $room = \App\Models\Room::find($this->roomId);

        // Jika kamar tidak ada, hentikan
        if (!$room) {
            return;
        }

        // Cek kembali: Pastikan kamar masih berstatus 'cleaning'
        // dan tidak ada aktivitas lain yang menimpanya secara tiba-tiba
        if ($room->status === 'cleaning') {
            $room->update(['status' => 'available']);
        }
    }
}
