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
    Schema::table('layanan', function (Blueprint $table) {
        // Kolom ini akan menyimpan array ID dari jenis_kendaraan yang diizinkan
        $table->json('jenis_kendaraan_akses')->nullable()->after('jenis_layanan');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            //
        });
    }
};
