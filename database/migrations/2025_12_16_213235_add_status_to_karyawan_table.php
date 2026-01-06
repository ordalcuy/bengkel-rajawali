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
        Schema::table('karyawan', function (Blueprint $table) {
            // Add status column after role
            $table->enum('status', ['aktif', 'cuti', 'sakit', 'tidak_aktif'])
                  ->default('aktif')
                  ->after('role')
                  ->comment('Status karyawan: aktif, cuti, sakit, tidak_aktif');
            
            // Add index for better query performance
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawan', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['status']);
            
            // Drop column
            $table->dropColumn('status');
        });
    }
};
