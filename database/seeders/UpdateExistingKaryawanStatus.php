<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Karyawan;
use App\Enums\StatusKaryawan;

class UpdateExistingKaryawanStatus extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update semua karyawan existing yang statusnya NULL atau empty
        // Set ke status Aktif sebagai default
        $updated = Karyawan::whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => StatusKaryawan::AKTIF->value]);
        
        // Also ensure all karyawan have aktif status as default
        // (in case migration already set default)
        $this->command->info("✅ Updated {$updated} existing karyawan to 'Aktif' status");
        
        $totalKaryawan = Karyawan::count();
        $aktifKaryawan = Karyawan::where('status', StatusKaryawan::AKTIF->value)->count();
        
        $this->command->info("📊 Total karyawan: {$totalKaryawan}, Aktif: {$aktifKaryawan}");
    }
}
