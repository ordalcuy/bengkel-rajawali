<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use Illuminate\Http\Request;
use App\Events\AntreanDipanggil;
use App\Events\WaitingListUpdated;
use App\Models\Karyawan;
use Carbon\Carbon;

class WaitingListController extends Controller
{
    public function getWaitingList()
    {
        $antreans = Antrean::where('status', 'Menunggu')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $listMenunggu = [
            'ringan' => [],
            'sedang' => [],
            'berat' => [],
        ];
        
        foreach ($antreans as $antrean) {
            $jenis = $antrean->getJenisLayananTerberat();
            if (isset($listMenunggu[$jenis])) {
                $listMenunggu[$jenis][] = $antrean->nomor_antrean;
            }
        }

        return response()->json($listMenunggu);
    }

    public function panggilManual(Request $request)
    {
        // 1. Cari antrean "Menunggu" terlama
        $antrean = Antrean::where('status', 'Menunggu')
                         ->orderBy('created_at', 'asc')
                         ->first();

        // =================================================================
        // [PERBAIKAN BUG]
        // Jika tidak ada antrean yang menunggu, jangan panggil "A001".
        // Langsung hentikan fungsi ini.
        // =================================================================
        if (!$antrean) {
            return response()->json([
                'message' => 'Tidak ada antrean untuk dipanggil.'
            ], 404); // Kirim 404 Not Found
        }
        // =================================================================
        // AKHIR PERBAIKAN
        // =================================================================

        // 2. Jika antrean ditemukan, proses seperti biasa
        // (Logika ini hanya akan berjalan jika $antrean ditemukan)
        
        // Coba tugaskan ke mekanik yang sedang idle
        $mekanikIdle = Karyawan::where('role', 'mekanik')
            ->whereDoesntHave('antrean', function ($query) {
                $query->where('status', 'Dikerjakan');
            })
            ->first();

        $antrean->status = 'Dikerjakan';
        $antrean->waktu_mulai = Carbon::now();
        $antrean->karyawan_id = $mekanikIdle?->id ?? $antrean->karyawan_id; // Tetapkan jika ada yg idle
        $antrean->save();

        // 3. Broadcast event (hanya untuk antrean yang valid)
        AntreanDipanggil::dispatch($antrean);
        WaitingListUpdated::dispatch();
        Antrean::broadcastActiveList();

        return response()->json($antrean);
    }
}