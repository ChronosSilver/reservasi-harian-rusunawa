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
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            // Menggunakan enum untuk memastikan hanya ada dua pilihan
            $table->enum('name', ['AC', 'Kipas'])->default('AC');
            $table->decimal('base_price', 10, 2);
            $table->decimal('extra_person_fee', 10, 2)->default(25000);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
