<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antrean;


class DisplayController extends Controller
{
    public function index()
    {
        // Ambil data awal saat halaman pertama kali dibuka
        $sedangDikerjakan = Antrean::where('status', 'Dikerjakan')
                                ->orderBy('waktu_mulai', 'desc')
                                ->first();
        $berikutnya = Antrean::where('status', 'Menunggu')
                            ->orderBy('created_at', 'asc')
                            ->first();

        return view('display', [
            'now_serving' => $sedangDikerjakan,
            'next_in_line' => $berikutnya,
        ]);
    }
}
