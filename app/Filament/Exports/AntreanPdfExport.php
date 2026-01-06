<?php

namespace App\Filament\Exports;

use App\Models\Antrean;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf; // <-- Tambahkan ini
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use PDF; // <-- [PERBAIKAN] Ini adalah baris yang memperbaiki error 'Unknown Class'

class AntreanPdfExport
{
    protected $startDate;
    protected $endDate;
    protected $mekanikId;
    protected $jenisLayanan;

    public function __construct($startDate = null, $endDate = null, $mekanikId = null, $jenisLayanan = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->mekanikId = $mekanikId;
        $this->jenisLayanan = $jenisLayanan;
    }

    public function download()
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $data = $this->getData();

        $viewData = [
            'antreans' => $data,
            'company_name' => $this->cleanString(config('bengkel.nama', 'Bengkel Rajawali Motor')),
            'company_address' => $this->cleanString(config('bengkel.alamat', 'Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang')),
            'company_phone' => $this->cleanString(config('bengkel.telepon', '085645523234')),
            'report_title' => $this->cleanString('Laporan Antrean Selesai'),
            'report_period' => $this->getPeriodText(),
            'total_antrean' => $data->count(),
            'print_time' => now()->format('d/m/Y H:i:s'),
        ];

        // Render view ke HTML
        $html = view('exports.laporan-antrean-pdf', $viewData)->render();

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
        }, 'laporan-antrean-selesai-' . now()->format('d-m-Y-H-i-s') . '.pdf');
    }

    /**
     * [TETAP DIPAKAI] Fungsi ini tetap kita gunakan untuk membersihkan data
     * sebelum dikirim ke view.
     */
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

    /**
     * [TETAP DIPAKAI] Fungsi ini tidak berubah.
     */
    private function getData(): Collection
    {
        $query = Antrean::where('status', 'Selesai')
            ->with([
                'pengunjung', 
                'kendaraan.pengunjung', 
                'karyawan',
                'layanan' // Pastikan ini singular
            ]);

        if ($this->startDate) {
            $query->whereDate('waktu_selesai', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('waktu_selesai', '<=', $this->endDate);
        }

        if ($this->mekanikId) {
            $query->where('karyawan_id', $this->mekanikId);
        }

        if ($this->jenisLayanan) {
            $query->whereHas('layanan', function ($q) {
                $q->where('jenis_layanan', $this->jenisLayanan);
            });
        }

        $data = $query->orderBy('waktu_selesai', 'desc')->get();
        
        return $data->map(function ($item) {
            $item->nomor_antrean = $this->cleanString($item->nomor_antrean);
            if ($item->pengunjung) {
                $item->pengunjung->nama_pengunjung = $this->cleanString($item->pengunjung->nama_pengunjung);
            }
            if ($item->kendaraan && $item->kendaraan->pengunjung) {
                $item->kendaraan->pengunjung->nama_pengunjung = $this->cleanString($item->kendaraan->pengunjung->nama_pengunjung);
            }
            if ($item->karyawan) {
                $item->karyawan->nama_karyawan = $this->cleanString($item->karyawan->nama_karyawan);
            }
            if ($item->layanan) {
                $item->layanan->each(function ($layanan) {
                    $layanan->nama_layanan = $this->cleanString($layanan->nama_layanan);
                });
            }
            return $item;
        });
    }

    /**
     * [TETAP DIPAKAI] Fungsi ini tidak berubah.
     */
    private function getPeriodText(): string
    {
        if ($this->startDate && $this->endDate) {
            return Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y');
        } elseif ($this->startDate) {
            return 'Dari ' . Carbon::parse($this->startDate)->format('d/m/Y');
        } elseif ($this->endDate) {
            return 'Sampai ' . Carbon::parse($this->endDate)->format('d/m/Y');
        }
        return 'Semua Periode';
    }
}