<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomType;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Tampilkan reservasi yang sedang aktif (belum selesai / batal).
     */
    public function index()
    {
        $reservations = Auth::user()->reservations()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['roomType', 'payments'])
            ->latest()
            ->get();
            
        return view('tenant.reservations.index', compact('reservations'));
    }

    /**
     * Tampilkan riwayat seluruh reservasi (terutama yang sudah selesai/batal).
     */
    public function history()
    {
        $reservations = Auth::user()->reservations()
            ->with(['roomType', 'payments'])
            ->latest()
            ->get();
            
        return view('tenant.reservations.history', compact('reservations'));
    }

    /**
     * Tampilkan form untuk membuat reservasi baru.
     */
    public function create(Request $request)
    {
        $roomTypes = RoomType::all();
        // Cek jika pengguna datang dari tombol 'Pesan Sekarang' di katalog kamar tertentu
        $selectedType = $request->query('type_id');

        return view('tenant.reservations.create', compact('roomTypes', 'selectedType'));
    }

    /**
     * Proses penyimpanan reservasi ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'duration_days' => 'required|integer|min:1|max:365',
            'guest_count' => 'required|integer|min:1|max:3',
            'payment_method' => 'required|in:transfer,cash',
        ], [
            'check_in_date.after_or_equal' => 'Tanggal masuk tidak boleh di masa lalu.',
            'payment_method.required' => 'Silakan pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        $roomType = RoomType::findOrFail($validated['room_type_id']);
        
        // Hitung Check Out Date (berdasarkan durasi hari)
        $checkInDate = \Carbon\Carbon::parse($validated['check_in_date']);
        $totalDays = (int) $validated['duration_days'];
        $checkOutDate = $checkInDate->copy()->addDays($totalDays);

        // Perhitungan Harga Dasar (Harian)
        $basePriceTotal = $roomType->base_price * $totalDays;
        
        $extraFeeTotal = 0;
        // Biaya ekstra dikenakan harian jika 3 orang
        if ($validated['guest_count'] == 3) {
            $extraFeeTotal = $roomType->extra_person_fee * $totalDays;
        }

        $totalPrice = $basePriceTotal + $extraFeeTotal;

        // Simpan Reservasi
        // (Event di model akan otomatis men-generate ticket_code dan payment pending)
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'room_type_id' => $roomType->id,
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $checkOutDate,
            'guest_count' => $validated['guest_count'],
            'total_price' => $totalPrice,
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        return redirect()->route('reservations.index')->with('success', 'Reservasi berhasil dibuat! Silakan lanjutkan ke pembayaran.');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        // Validasi input alasan
        $request->validate([
            'cancellation_reason' => 'required|string|max:1000'
        ]);

        // Pastikan hanya pemilik yang bisa membatalkan
        if ($reservation->user_id !== auth()->id()) {
            abort(403, 'Anda tidak diizinkan membatalkan reservasi ini.');
        }

        // Pastikan hanya bisa membatalkan yang berstatus pending
        if ($reservation->status !== 'pending') {
            return back()->withErrors(['msg' => 'Hanya reservasi berstatus Pending yang dapat dibatalkan.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation, $request) {
            // Ubah status reservasi
            $reservation->update(['status' => 'cancelled']);

            // Update status pembayaran terkait beserta alasan pembatalannya
            $payment = $reservation->payments()->where('status', 'pending')->first();
            if ($payment) {
                $payment->update([
                    'status' => 'rejected', // Di tabel payments tidak ada 'cancelled', kita gunakan 'rejected'
                    'cancellation_reason' => $request->cancellation_reason
                ]);
            }
        });

        return back()->with('success', 'Reservasi berhasil dibatalkan beserta alasannya tercatat.');
    }
}
