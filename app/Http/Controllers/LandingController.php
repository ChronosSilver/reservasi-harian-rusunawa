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
        // Mengambil semua tipe kamar dari database
        $roomTypes = RoomType::all();

        return view('welcome', compact('roomTypes'));
    }
}
