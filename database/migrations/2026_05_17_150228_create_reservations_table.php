<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            // 1. Relasi Kunci
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            // 2. Dimensi Waktu Kontrak
            $table->date('check_in_date');
            $table->date('check_out_date');
            // 3. Dimensi Waktu Realitas
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            // 4. Aturan Bisnis & Finansial (Nilai Tagihan)
            $table->integer('guest_count')->default(1);
            $table->decimal('total_price', 10, 2); 
            // 5. Siklus Hidup Operasional (Tanpa Status Pembayaran)
            $table->enum('status', ['pending', 'confirmed', 'active', 'completed', 'cancelled'])->default('pending');
            // 6. Fleksibilitas Komunikasi
            $table->text('notes')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
