<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('bank_account')->nullable(); // Nomor Rekening Pengirim/Tujuan Refund
            $table->string('bank_name')->nullable(); // Nama Bank Pengirim/Tujuan Refund
            $table->string('payment_proof')->nullable(); // Boleh kosong jika bayar tunai (cash)
            $table->string('refund_proof')->nullable(); // Bukti transfer pengembalian dana
            $table->text('cancellation_reason')->nullable(); // Alasan pembatalan dari penyewa
            $table->enum('status', ['pending', 'paid', 'verified', 'rejected', 'refunded'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->timestamps(); 
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
