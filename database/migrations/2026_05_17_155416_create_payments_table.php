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
            $table->string('sender_name')->nullable(); // Nama Pengirim di Rekening
            $table->string('bank_name')->nullable(); // Nama Bank Pengirim
            $table->string('payment_proof')->nullable(); // Boleh kosong jika bayar tunai (cash)
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
