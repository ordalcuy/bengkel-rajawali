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
            // Tambahkan kolom pengunjung_id setelah kendaraan_id
            $table->foreignId('pengunjung_id')
                  ->nullable()
                  ->after('kendaraan_id')
                  ->constrained('pengunjung')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antrean', function (Blueprint $table) {
            $table->dropForeign(['pengunjung_id']);
            $table->dropColumn('pengunjung_id');
        });
    }
};