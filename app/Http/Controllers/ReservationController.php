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
        // Cek kelengkapan identitas profil penyewa
        if (empty(Auth::user()->identity_type) || empty(Auth::user()->identity_number)) {
            return redirect()->route('profile.index')
                ->with('require_identity', true);
        }

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
        // Cek kelengkapan identitas (pengamanan ekstra)
        if (empty(Auth::user()->identity_type) || empty(Auth::user()->identity_number)) {
            return redirect()->route('profile.index')
                ->with('require_identity', true);
        }

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

        // Cek Kuota Ketersediaan Fisik Kamar
        $totalRooms = \App\Models\Room::where('room_type_id', $roomType->id)
            ->where('status', '!=', 'maintenance')
            ->count();
            
        $overlappingReservations = Reservation::where('room_type_id', $roomType->id)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in_date', '<', $checkOutDate)
            ->where('check_out_date', '>', $checkInDate)
            ->count();
        
        if ($overlappingReservations >= $totalRooms) {
            return back()->withErrors(['room_type_id' => "Mohon maaf, tidak ada kamar bertipe {$roomType->name} yang tersedia secara penuh pada rentang tanggal tersebut."])->withInput();
        }

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

        // Pastikan hanya bisa membatalkan yang berstatus pending atau confirmed
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['msg' => 'Hanya reservasi berstatus Pending atau Confirmed yang dapat dibatalkan.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($reservation, $request) {
            if ($reservation->status === 'confirmed') {
                // Ubah status menjadi refunding untuk diproses admin
                $reservation->update(['status' => 'refunding']);
                
                $payment = $reservation->payments()->whereIn('status', ['paid', 'verified'])->first();
                if ($payment) {
                    $payment->update([
                        'cancellation_reason' => 'Pengajuan Refund: ' . $request->cancellation_reason
                    ]);
                }
            } else {
                // Pending
                $reservation->update(['status' => 'cancelled']);
                
                $payment = $reservation->payments()->where('status', 'pending')->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'rejected',
                        'cancellation_reason' => $request->cancellation_reason
                    ]);
                }
            }
        });

        if ($reservation->status === 'refunding') {
            return back()->with('success', 'Pengajuan pembatalan berhasil! Status reservasi kini menjadi "Refunding". Admin akan memproses pengembalian dana Anda segera.');
        }

        return back()->with('success', 'Reservasi berhasil dibatalkan beserta alasannya tercatat.');
    }

    public function payment(Reservation $reservation)
    {
        // Pastikan milik tenant yang sedang login
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Pastikan status masih pending
        if ($reservation->status !== 'pending') {
            return redirect()->route('reservations.index')->withErrors(['msg' => 'Reservasi ini sudah tidak dalam status pending.']);
        }

        $bankAccounts = \App\Models\BankAccount::all();
        $payment = $reservation->payments()->where('status', 'pending')->first();

        return view('tenant.reservations.payment', compact('reservation', 'bankAccounts', 'payment'));
    }

    public function uploadPayment(Request $request, Reservation $reservation)
    {
        // Pastikan milik tenant yang sedang login
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'payment_proof' => 'required|image|max:2048',
        ]);

        $payment = $reservation->payments()->where('status', 'pending')->first();

        if (!$payment) {
            return back()->withErrors(['msg' => 'Tidak ditemukan tagihan pending untuk reservasi ini.']);
        }

        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('bukti-transfer', 'public');
            
            $payment->update([
                'bank_account_id' => $request->bank_account_id,
                'payment_proof' => $path,
                'status' => 'paid',
                'payment_date' => now(),
            ]);
        }

        return redirect()->route('reservations.index')->with('success', 'Bukti pembayaran berhasil diunggah. Silakan tunggu verifikasi admin.');
    }

    /**
     * Cek Ketersediaan Kamar (Untuk AJAX Request)
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'duration_days' => 'required|integer|min:1',
        ]);

        $typeId = $request->room_type_id;
        $checkIn = \Carbon\Carbon::parse($request->check_in_date);
        $checkOut = $checkIn->copy()->addDays((int) $request->duration_days);

        $totalRooms = \App\Models\Room::where('room_type_id', $typeId)
            ->where('status', '!=', 'maintenance')
            ->count();

        $overlappingReservations = Reservation::where('room_type_id', $typeId)
            ->whereIn('status', ['pending', 'confirmed', 'active'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->count();

        $available = max(0, $totalRooms - $overlappingReservations);

        return response()->json([
            'available' => $available,
            'total' => $totalRooms
        ]);
    }
}
