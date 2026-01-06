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
        Schema::create('antrean_layanan', function (Blueprint $table) {
            $table->id();

            // --- PERBAIKAN DENGAN SINTAKS EKSPLISIT ---
            
            // 1. Buat kolom untuk antrean_id
            $table->unsignedBigInteger('antrean_id'); 
            
            // 2. Definisikan secara manual foreign key ke tabel 'antrean'
            $table->foreign('antrean_id')
                  ->references('id')
                  ->on('antrean') // <-- Nama tabel yang benar
                  ->onDelete('cascade');

            // Foreign key untuk layanan sudah benar, tapi kita buat eksplisit juga
            $table->unsignedBigInteger('layanan_id');
            $table->foreign('layanan_id')
                  ->references('id')
                  ->on('layanan')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrean_layanan');
    }
};