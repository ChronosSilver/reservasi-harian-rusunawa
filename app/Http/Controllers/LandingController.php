<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomType;

class LandingController extends Controller
{
    /**
     * Menampilkan halaman beranda dengan katalog kamar.
     */
    public function index()
    {
        // Mengambil semua tipe kamar dari database beserta jumlah kamarnya (kecuali maintenance)
        $roomTypes = RoomType::withCount(['rooms' => function ($query) {
            $query->where('status', '!=', 'maintenance');
        }])->get();

        return view('welcome', compact('roomTypes'));
    }
}
