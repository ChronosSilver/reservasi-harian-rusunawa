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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            //HAPUS fungsi ->unique() dari kolom ini
            $table->string('room_number'); 
            $table->enum('building', ['Rusunawa Putri', 'Rusun Inn', 'Rusunawa Putra'])->default('Rusunawa Putri');
            $table->integer('capacity')->default(3);
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance'])->default('available');
            $table->timestamps();
            //TAMBAHKAN INI: Deklarasi Kunci Unik Komposit
            $table->unique(['building', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
