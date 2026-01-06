<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use Illuminate\Http\Request;

class AntreanPrintController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Antrean $antrean)
    {
        // LANGKAH 1: Paksa muat semua relasi yang mungkin dibutuhkan.
        $antrean->load(['pengunjung', 'kendaraan.pengunjung', 'layanan', 'karyawan']);

        // LANGKAH 2: Terapkan logika cerdas untuk menentukan nama pelanggan.
        $namaPelanggan = 'N/A'; // Default value

        // Prioritas 1: Cek pelanggan yang tercatat langsung di antrean (untuk data baru/fleksibel).
        if ($antrean->pengunjung) {
            $namaPelanggan = $antrean->pengunjung->nama_pengunjung;
        } 
        // Prioritas 2: Jika tidak ada, gunakan pemilik asli kendaraan (untuk data lama).
        elseif ($antrean->kendaraan && $antrean->kendaraan->pengunjung) {
            $namaPelanggan = $antrean->kendaraan->pengunjung->nama_pengunjung;
        }

        // Kirim semua data yang diperlukan ke view, termasuk nama pelanggan yang sudah benar.
        return view('cetak.antrean', [
            'antrean' => $antrean,
            'nama_pelanggan' => $namaPelanggan,
        ]);
    }
}