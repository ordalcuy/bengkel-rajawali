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
    Schema::table('kendaraan', function (Blueprint $table) {
        $table->dropColumn('jenis'); // Hapus kolom 'jenis' yang lama
        // Tambahkan foreign key ke tabel jenis_kendaraan
        $table->foreignId('jenis_kendaraan_id')->nullable()->after('merk')->constrained('jenis_kendaraan');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
