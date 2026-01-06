<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AntreanLacakController extends Controller
{
    /**
     * Halaman /lacak (jika masih ingin dipertahankan)
     */
    public function index()
    {
        return view('lacak');
    }

    /**
     * Logika untuk memproses form pelacakan
     */
    public function lacak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_antrean' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->route('welcome')
                ->withErrors($validator)
                ->withInput();
        }

        $nomorAntrean = strtoupper($request->nomor_antrean);

        $antrean = Antrean::where('nomor_antrean', $nomorAntrean)
                        ->whereDate('created_at', Carbon::today())
                        ->with(['karyawan', 'pengunjung'])
                        ->first();

        if ($antrean) {
            return redirect()->route('welcome')->with('antrean_lacak', $antrean);
        }

        return redirect()->route('welcome')->with('error_lacak', 'Antrean tidak ditemukan atau sudah selesai kemarin.');
    }

    /**
     * Show specific antrean (jika masih diperlukan)
     */
    public function show($nomor_antrean)
    {
        $antrean = Antrean::where('nomor_antrean', $nomor_antrean)
                        ->whereDate('created_at', Carbon::today())
                        ->with(['karyawan', 'pengunjung'])
                        ->firstOrFail();

        return view('lacak-show', compact('antrean'));
    }
}