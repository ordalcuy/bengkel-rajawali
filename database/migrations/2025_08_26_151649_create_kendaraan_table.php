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
    Schema::create('kendaraan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pengunjung_id')->constrained('pengunjung')->cascadeOnDelete();
        $table->string('nomor_plat')->unique();
        $table->string('merk');
        $table->string('jenis');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
