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
            $table->string('merk')->nullable()->change();
        });

        Schema::table('pengunjung', function (Blueprint $table) {
            $table->string('nomor_tlp')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendaraan', function (Blueprint $table) {
            $table->string('merk')->nullable(false)->change();
        });

        Schema::table('pengunjung', function (Blueprint $table) {
            $table->string('nomor_tlp')->nullable(false)->change();
        });
    }
};
