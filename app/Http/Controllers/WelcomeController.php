<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Layanan;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    public function index()
    {
        // Ambil data antrean aktif HARI INI untuk ditampilkan di tabel
        $antreanAktif = Antrean::with(['pengunjung', 'karyawan', 'layanan', 'kendaraan'])
            ->whereIn('status', ['Menunggu', 'Dikerjakan', 'Check-in'])
            ->whereDate('created_at', Carbon::today()) // Hanya antrean hari ini
            ->orderByRaw("FIELD(status, 'Dikerjakan', 'Check-in', 'Menunggu')")
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Hitung statistik
        $totalAntreanHariIni = Antrean::whereDate('created_at', Carbon::today())->count();
        $mekanikAktif = Karyawan::where('role', 'mekanik')->count();
        
        // Hitung estimasi rata-rata berdasarkan antrean yang sedang menunggu
        $antreanMenunggu = Antrean::where('status', 'Menunggu')->count();
        $estimasiRataRata = $antreanMenunggu * 45; // 45 menit per antrean

        // Ambil data layanan
        $layanan = Layanan::all();

        return view('welcome', compact(
            'antreanAktif',
            'totalAntreanHariIni',
            'mekanikAktif',
            'estimasiRataRata',
            'layanan'
        ));
    }

    public function lacak(Request $request)
    {
        $request->validate([
            'nomor_antrean' => 'required|string|max:10',
            'tanggal_filter' => 'nullable|in:hari_ini,kemarin'
        ]);

        $nomorAntrean = strtoupper(trim($request->nomor_antrean));
        $tanggalFilter = $request->tanggal_filter ?? 'hari_ini';
        
        // Tentukan tanggal berdasarkan filter
        $tanggalCari = $tanggalFilter === 'kemarin' 
            ? Carbon::yesterday() 
            : Carbon::today();

        // Query dengan filter tanggal
        $antrean = Antrean::with([
                'pengunjung', 
                'karyawan', 
                'kendaraan', 
                'layanan'
            ])
            ->where('nomor_antrean', $nomorAntrean)
            ->whereDate('created_at', $tanggalCari)
            ->orderByRaw("CASE 
                WHEN status = 'Dikerjakan' THEN 1
                WHEN status = 'Menunggu' THEN 2
                WHEN status = 'Selesai' THEN 3
                ELSE 4
            END")
            ->first();

        if ($antrean) {
            // Redirect ke halaman detail menggunakan ID (unik)
            return redirect()->route('lacak.show.id', ['id' => $antrean->id]);
        }

        $tanggalLabel = $tanggalFilter === 'kemarin' ? 'kemarin' : 'hari ini';
        
        return redirect('/#cek-antrean')
            ->with('error_lacak', 'Nomor antrean ' . $nomorAntrean . ' tidak ditemukan untuk ' . $tanggalLabel . '.')
            ->withInput();
    }

    public function showLacak($nomor_antrean)
    {
        // SMART QUERY: Handle duplicate queue numbers
        // Priority 1: Active queues (Menunggu/Dikerjakan)
        // Priority 2: Most recent queue if multiple exist
        $antrean = Antrean::with(['pengunjung', 'karyawan', 'kendaraan', 'layanan'])
            ->where('nomor_antrean', $nomor_antrean)
            ->orderByRaw("CASE 
                WHEN status = 'Dikerjakan' THEN 1
                WHEN status = 'Menunggu' THEN 2
                WHEN status = 'Selesai' THEN 3
                ELSE 4
            END")
            ->orderBy('created_at', 'desc') // Terbaru jika status sama
            ->first();

        if (!$antrean) {
            return redirect('/#cek-antrean')
                ->with('error_lacak', 'Nomor antrean ' . $nomor_antrean . ' tidak ditemukan.');
        }

        // Jika diakses via QR code, tampilkan view khusus untuk hasil lacakan
        return view('lacak-result', compact('antrean'));
    }

    /**
     * Lacak antrean berdasarkan ID (dari QR Code)
     * Ini UNIK dan tidak ada duplikasi
     */
    public function showLacakById($id)
    {
        $antrean = Antrean::with(['pengunjung', 'karyawan', 'kendaraan', 'layanan'])
            ->find($id);

        if (!$antrean) {
            return redirect('/#cek-antrean')
                ->with('error_lacak', 'Data antrean tidak ditemukan.');
        }

        return view('lacak-result', compact('antrean'));
    }

    public function getAntreanAktif()
    {
        $antreanAktif = Antrean::with(['pengunjung', 'karyawan', 'kendaraan', 'layanan'])
            ->whereIn('status', ['Menunggu', 'Dikerjakan', 'Check-in'])
            ->orderByRaw("FIELD(status, 'Dikerjakan', 'Check-in', 'Menunggu')")
            ->orderBy('waktu_mulai', 'asc')
            ->get()
            ->map(function($antrean) {
                // Format jenis layanan
                $jenisLayanan = $antrean->layanan->pluck('jenis_layanan')
                    ->unique()
                    ->map(function($jenis) {
                        return match($jenis) {
                            'ringan' => 'Ringan',
                            'sedang' => 'Sedang',
                            'berat' => 'Berat',
                            default => ucfirst($jenis)
                        };
                    })->values()->all();

                return [
                    'nomor_antrean' => $antrean->nomor_antrean,
                    'plat_nomor' => $antrean->kendaraan->nomor_plat ?? 'N/A',
                    'status' => $antrean->status,
                    'mekanik' => $antrean->karyawan->nama_karyawan ?? 'Belum ditentukan',
                    'estimasi_waktu' => $this->hitungEstimasiWaktu($antrean),
                    'waktu_checkin' => $antrean->created_at->format('H:i'),
                    'jenis_layanan' => $jenisLayanan
                ];
            });

        return response()->json($antreanAktif);
    }

    public function getStatistik()
    {
        $totalAntreanHariIni = Antrean::whereDate('created_at', Carbon::today())->count();
        $mekanikAktif = Karyawan::where('role', 'mekanik')->count();
        $antreanMenunggu = Antrean::where('status', 'Menunggu')->count();
        $antreanDikerjakan = Antrean::where('status', 'Dikerjakan')->count();
        $antreanSelesaiHariIni = Antrean::where('status', 'Selesai')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        $estimasiRataRata = $antreanMenunggu * 45; // 45 menit per antrean

        return response()->json([
            'total_antrean_hari_ini' => $totalAntreanHariIni,
            'mekanik_aktif' => $mekanikAktif,
            'estimasi_rata_rata' => $estimasiRataRata,
            'antrean_menunggu' => $antreanMenunggu,
            'antrean_dikerjakan' => $antreanDikerjakan,
            'antrean_selesai_hari_ini' => $antreanSelesaiHariIni
        ]);
    }

    private function hitungEstimasiWaktu($antrean)
    {
        if ($antrean->status == 'Dikerjakan') {
            return 'Segera selesai';
        }

        // Hitung posisi antrean dalam antrian
        $posisi = Antrean::where('status', 'Menunggu')
            ->where('created_at', '<', $antrean->created_at)
            ->count() + 1;

        $estimasiMenit = $posisi * 45; // 45 menit per antrean

        if ($estimasiMenit < 60) {
            return $estimasiMenit . ' Menit';
        } else {
            $jam = floor($estimasiMenit / 60);
            $menit = $estimasiMenit % 60;
            return $jam . ' Jam ' . $menit . ' Menit';
        }
    }
}