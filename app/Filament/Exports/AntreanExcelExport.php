<?php

namespace App\Filament\Exports;

use App\Models\Antrean;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class AntreanExcelExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
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

    public function query()
    {
        $query = Antrean::where('status', 'Selesai')
            ->with([
                'pengunjung', 
                'kendaraan.pengunjung', 
                'karyawan'
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

        return $query->orderBy('waktu_selesai', 'desc');
    }

    public function headings(): array
    {
        return [
            // Header Company
            [config('bengkel.nama', 'Bengkel Rajawali Motor')],
            [config('bengkel.alamat', 'Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang')],
            ['Telp: ' . config('bengkel.telepon', '085645523234')],
            [''],
            ['LAPORAN ANTREAN SELESAI'],
            ['Periode: ' . $this->getPeriodText()],
            [''],
            // Table Headers
            [
                'No. Antrean',
                'Pelanggan',
                'Plat Nomor', 
                'Jenis Layanan',
                'Mekanik',
                'Tanggal Mulai',
                'Tanggal Selesai', 
                'Durasi'
            ]
        ];
    }

    public function map($antrean): array
    {
        return [
            $antrean->nomor_antrean,
            $this->getNamaPelanggan($antrean),
            $antrean->kendaraan->nomor_plat ?? 'N/A',
            $this->getLayananText($antrean),
            $antrean->karyawan->nama_karyawan ?? 'N/A',
            $antrean->waktu_mulai ? Carbon::parse($antrean->waktu_mulai)->format('d/m/Y H:i') : 'N/A',
            $antrean->waktu_selesai ? Carbon::parse($antrean->waktu_selesai)->format('d/m/Y H:i') : 'N/A',
            $this->calculateDurasi($antrean)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk company header
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => 'center']
            ],
            2 => [
                'font' => ['size' => 12],
                'alignment' => ['horizontal' => 'center']
            ],
            3 => [
                'font' => ['size' => 12],
                'alignment' => ['horizontal' => 'center']
            ],
            // Style untuk judul laporan
            5 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center']
            ],
            6 => [
                'font' => ['size' => 12],
                'alignment' => ['horizontal' => 'center']
            ],
            // Style untuk table header - warna biru #3b82f6
            8 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ],
            // Style untuk data rows dengan border
            'A9:H1000' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin',
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => 'top',
                    'wrapText' => true
                ]
            ],
            // Style khusus untuk kolom Jenis Layanan (D) - alignment kiri
            'D9:D1000' => [
                'alignment' => [
                    'horizontal' => 'left',
                    'vertical' => 'top',
                    'wrapText' => true
                ]
            ],
            // Style untuk kolom lainnya - alignment center
            'A9:A1000' => ['alignment' => ['horizontal' => 'center', 'vertical' => 'top']],
            'B9:B1000' => ['alignment' => ['horizontal' => 'center', 'vertical' => 'top']],
            'C9:C1000' => ['alignment' => ['horizontal' => 'center', 'vertical' => 'top']],
            'E9:H1000' => ['alignment' => ['horizontal' => 'center', 'vertical' => 'top']],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge cells untuk header
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->mergeCells('A2:H2');
                $event->sheet->mergeCells('A3:H3');
                $event->sheet->mergeCells('A5:H5');
                $event->sheet->mergeCells('A6:H6');

                // Set lebar kolom yang optimal
                $columnWidths = [
                    'A' => 15, // No. Antrean
                    'B' => 25, // Pelanggan
                    'C' => 15, // Plat Nomor
                    'D' => 35, // Jenis Layanan (paling lebar)
                    'E' => 20, // Mekanik
                    'F' => 18, // Tanggal Mulai
                    'G' => 18, // Tanggal Selesai
                    'H' => 15, // Durasi
                ];

                foreach ($columnWidths as $column => $width) {
                    $event->sheet->getColumnDimension($column)->setWidth($width);
                }

                // Set tinggi row untuk header tabel
                $event->sheet->getRowDimension(8)->setRowHeight(25);

                // Set tinggi row minimum untuk data rows
                $dataStartRow = 9;
                $dataEndRow = $this->query()->count() + 8;
                
                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    $event->sheet->getRowDimension($row)->setRowHeight(30); // Minimum height 30
                }

                // Enable wrap text untuk semua cell data
                $event->sheet->getStyle('A9:G1000')
                    ->getAlignment()
                    ->setWrapText(true);

                // Tambahkan total row di akhir
                $totalRow = $dataEndRow + 1;
                $event->sheet->setCellValue("A{$totalRow}", "Total Antrean: " . $this->query()->count());
                $event->sheet->mergeCells("A{$totalRow}:H{$totalRow}");
                $event->sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2F2F2']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // Tambahkan border untuk total row
                $event->sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Tambahkan timestamp
                $timestampRow = $totalRow + 1;
                $event->sheet->setCellValue("A{$timestampRow}", "Dicetak pada: " . now()->format('d/m/Y H:i:s'));
                $event->sheet->mergeCells("A{$timestampRow}:H{$timestampRow}");
                $event->sheet->getStyle("A{$timestampRow}")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => 'center']
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Laporan Antrean';
    }

    private function getNamaPelanggan($antrean): string
    {
        if ($antrean->pengunjung) {
            return $antrean->pengunjung->nama_pengunjung;
        }
        if ($antrean->kendaraan && $antrean->kendaraan->pengunjung) {
            return $antrean->kendaraan->pengunjung->nama_pengunjung;
        }
        return 'N/A';
    }

    private function getLayananText($antrean): string
    {
        // Ambil jenis layanan yang unik
        $jenisLayanan = DB::table('antrean_layanan')
            ->join('layanan', 'antrean_layanan.layanan_id', '=', 'layanan.id')
            ->where('antrean_layanan.antrean_id', $antrean->id)
            ->pluck('layanan.jenis_layanan')
            ->unique()
            ->map(function($jenis) {
                return match($jenis) {
                    'ringan' => 'Servis Ringan',
                    'sedang' => 'Servis Sedang',
                    'berat' => 'Servis Berat',
                    default => ucfirst($jenis)
                };
            })
            ->toArray();
        
        if (empty($jenisLayanan)) {
            return 'N/A';
        }

        return implode(', ', $jenisLayanan);
    }

    private function calculateDurasi($antrean): string
    {
        if ($antrean->waktu_mulai && $antrean->waktu_selesai) {
            $mulai = Carbon::parse($antrean->waktu_mulai);
            $selesai = Carbon::parse($antrean->waktu_selesai);
            
            $diff = $mulai->diff($selesai);
            $parts = [];

            if ($diff->h > 0) $parts[] = $diff->h . ' jam';
            if ($diff->i > 0) $parts[] = $diff->i . ' menit';
            if (empty($parts) && $diff->s > 0) $parts[] = $diff->s . ' detik';
            
            return implode(' ', $parts) ?: 'kurang dari semenit';
        }
        return 'N/A';
    }

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