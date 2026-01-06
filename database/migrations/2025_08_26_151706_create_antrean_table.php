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
    Schema::create('antrean', function (Blueprint $table) {
        $table->id();
        $table->string('nomor_antrean')->nullable();        $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnDelete();
        $table->foreignId('layanan_id')->constrained('layanan');
        $table->foreignId('karyawan_id')->nullable()->constrained('karyawan')->nullOnDelete();
        $table->string('status')->default('Menunggu');
        $table->timestamp('waktu_mulai')->nullable();
        $table->timestamp('waktu_selesai')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrean');
    }
};
