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
        Schema::table('antrean', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['layanan_id']);
            // Hapus kolomnya
            $table->dropColumn('layanan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrean', function (Blueprint $table) {
            // Jika ingin rollback, tambahkan kembali kolomnya
            $table->foreignId('layanan_id')->nullable()->after('pengunjung_id')->constrained('layanan');
        });
    }
};