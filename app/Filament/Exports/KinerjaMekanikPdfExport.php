<?php

namespace App\Filament\Exports;

use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class KinerjaMekanikPdfExport
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? Carbon::today();
        $this->endDate = $endDate ?? Carbon::today();
    }

    public function download()
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $data = $this->getData();

        // Calculate totals for summary
        $totalServis = $data->sum('total_servis');
        $totalDurasiMenit = $data->sum('total_durasi_menit');
        $avgDurasi = $totalServis > 0 ? round($totalDurasiMenit / $totalServis) : 0;
        
        // Mekanik Teraktif = MAX(total_durasi) - mekanik dengan durasi kerja terbanyak
        $topMekanik = $data->sortByDesc('total_durasi_menit')->first();

        $viewData = [
            'mekaniks' => $data,
            'company_name' => $this->cleanString(config('bengkel.nama', 'Bengkel Rajawali Motor')),
            'company_address' => $this->cleanString(config('bengkel.alamat', 'Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang')),
            'company_phone' => $this->cleanString(config('bengkel.telepon', '085645523234')),
            'report_title' => $this->cleanString('Laporan Kinerja Mekanik'),
            'report_period' => $this->getPeriodText(),
            'total_servis' => $totalServis,
            'avg_durasi' => $avgDurasi,
            'top_mekanik' => $topMekanik ? $topMekanik->nama_karyawan : '-',
            'print_time' => now()->format('d/m/Y H:i:s'),
        ];

        // Render view ke HTML
        $html = view('exports.laporan-kinerja-mekanik-pdf', $viewData)->render();

        // Setup mPDF
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'orientation' => 'L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5,
            'fontDir' => array_merge($fontDirs, [
                base_path('resources/fonts'),
            ]),
            'fontdata' => $fontData + [
                'dejavusans' => [
                    'R' => 'DejaVuSans.ttf',
                    'I' => 'DejaVuSans-Oblique.ttf',
                ]
            ],
            'default_font' => 'dejavusans',
            'tempDir' => storage_path('app/mpdf/tmp'),
        ]);

        $mpdf->WriteHTML($html);
        
        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, 'laporan-kinerja-mekanik-' . now()->format('d-m-Y-H-i-s') . '.pdf');
    }

    private function cleanString(?string $string): string
    {
        if (is_null($string) || $string === '') {
            return '';
        }
        if (!mb_detect_encoding($string, 'UTF-8', true)) {
             $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        $string = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
        return $string ?: '';
    }

    private function getData(): Collection
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $mekaniks = Karyawan::whereIn('role', ['mekanik', 'helper'])
            ->where('status', 'aktif')
            ->get();

        return $mekaniks->map(function ($mekanik) use ($startDate, $endDate) {
            // Total servis
            $servisSelesai = Antrean::where('karyawan_id', $mekanik->id)
                ->where('status', 'Selesai')
                ->whereDate('waktu_selesai', '>=', $startDate)
                ->whereDate('waktu_selesai', '<=', $endDate)
                ->whereNotNull('waktu_mulai')
                ->whereNotNull('waktu_selesai')
                ->get();

            $totalServis = $servisSelesai->count();
            
            // Total durasi
            $totalDurasiMenit = 0;
            foreach ($servisSelesai as $antrean) {
                $mulai = Carbon::parse($antrean->waktu_mulai);
                $selesai = Carbon::parse($antrean->waktu_selesai);
                $totalDurasiMenit += $mulai->diffInMinutes($selesai);
            }
            
            // Rata-rata durasi
            $avgDurasiMenit = $totalServis > 0 ? round($totalDurasiMenit / $totalServis) : 0;
            
            // Servis per jenis
            $servisRingan = Antrean::where('karyawan_id', $mekanik->id)
                ->where('status', 'Selesai')
                ->whereDate('waktu_selesai', '>=', $startDate)
                ->whereDate('waktu_selesai', '<=', $endDate)
                ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'ringan'))
                ->count();
            
            $servisSedang = Antrean::where('karyawan_id', $mekanik->id)
                ->where('status', 'Selesai')
                ->whereDate('waktu_selesai', '>=', $startDate)
                ->whereDate('waktu_selesai', '<=', $endDate)
                ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'sedang'))
                ->count();
            
            $servisBerat = Antrean::where('karyawan_id', $mekanik->id)
                ->where('status', 'Selesai')
                ->whereDate('waktu_selesai', '>=', $startDate)
                ->whereDate('waktu_selesai', '<=', $endDate)
                ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'berat'))
                ->count();

            $mekanik->total_servis = $totalServis;
            $mekanik->total_durasi_menit = $totalDurasiMenit;
            $mekanik->total_durasi_text = $this->formatDurasi($totalDurasiMenit);
            $mekanik->avg_durasi_menit = $avgDurasiMenit;
            $mekanik->avg_durasi_text = $this->formatDurasi($avgDurasiMenit);
            $mekanik->servis_ringan = $servisRingan;
            $mekanik->servis_sedang = $servisSedang;
            $mekanik->servis_berat = $servisBerat;

            return $mekanik;
        })->sortByDesc('total_servis')->values();
    }

    private function formatDurasi($menit): string
    {
        if ($menit == 0) return '-';
        
        $jam = floor($menit / 60);
        $sisaMenit = $menit % 60;
        
        if ($jam > 0) {
            return $jam . 'j ' . $sisaMenit . 'm';
        }
        return $sisaMenit . 'm';
    }

    private function getPeriodText(): string
    {
        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->format('d/m/Y');
            $end = Carbon::parse($this->endDate)->format('d/m/Y');
            return $start === $end ? $start : $start . ' - ' . $end;
        }
        return 'Hari Ini';
    }
}
