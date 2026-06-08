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
            $table->string('payment_code')->unique()->nullable(); // Contoh: INV-260607-XXXX
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete(); // Rekening Bank Rusunawa
            $table->decimal('amount', 10, 2);
            $table->string('payment_proof')->nullable(); // Boleh kosong jika bayar tunai (cash)
            $table->string('refund_proof')->nullable(); // Bukti transfer pengembalian dana
            $table->text('cancellation_reason')->nullable(); // Alasan pembatalan dari penyewa
            $table->enum('status', ['pending', 'paid', 'verified', 'rejected', 'refunded'])->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('verified_at')->nullable(); // Waktu admin memverifikasi pembayaran
            $table->timestamps(); 
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
