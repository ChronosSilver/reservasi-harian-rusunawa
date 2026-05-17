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
            // 1. Foreign Key: Menghubungkan secara absolut ke induknya (Tabel Reservations)
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            // 2. Data Finansial
            $table->decimal('amount', 10, 2); 
            // 3. Metode dan Bukti Fisik
            $table->enum('payment_method', ['transfer', 'cash']);
            $table->string('payment_proof')->nullable(); // Boleh kosong jika bayar tunai (cash)
            // 4. Siklus Hidup Finansial
            $table->enum('status', ['pending', 'verified', 'rejected', 'refunded'])->default('pending');
            // 5. Audit Trail Waktu Transaksi
            $table->timestamp('payment_date')->nullable();
            $table->timestamps(); 
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
