<?php

namespace App\Filament\Exports;

use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KinerjaMekanikExcelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $data;
    protected $rowNumber = 0;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate ?? Carbon::today();
        $this->endDate = $endDate ?? Carbon::today();
        $this->data = $this->calculateData();
    }

    public function collection()
    {
        return $this->data;
    }

    private function calculateData()
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

            return (object) [
                'nama_karyawan' => $mekanik->nama_karyawan,
                'total_servis' => $totalServis,
                'total_durasi_menit' => $totalDurasiMenit,
                'avg_durasi_menit' => $avgDurasiMenit,
                'servis_ringan' => $servisRingan,
                'servis_sedang' => $servisSedang,
                'servis_berat' => $servisBerat,
            ];
        })->sortByDesc('total_servis')->values();
    }

    public function headings(): array
    {
        // Calculate summary data
        $totalServis = $this->data->sum('total_servis');
        $totalDurasi = $this->data->sum('total_durasi_menit');
        $avgDurasi = $totalServis > 0 ? round($totalDurasi / $totalServis) : 0;
        $topMekanik = $this->data->sortByDesc('total_durasi_menit')->first();

        return [
            // Header Company
            [config('bengkel.nama', 'Bengkel Rajawali Motor')],
            [config('bengkel.alamat', 'Jl. Mertojoyo Selatan No. 4A, Merjosari, Lowokwaru, Kota Malang')],
            ['Telp: ' . config('bengkel.telepon', '085645523234')],
            [''],
            ['LAPORAN KINERJA MEKANIK'],
            ['Periode: ' . $this->getPeriodText()],
            [''],
            ['Ringkasan: Total Servis: ' . $totalServis . ' | Rata-rata Durasi: ' . $avgDurasi . ' menit | Mekanik Teraktif: ' . ($topMekanik ? $topMekanik->nama_karyawan : '-')],
            [''],
            // Table Headers
            [
                'No',
                'Nama Mekanik',
                'Total Servis', 
                'Total Durasi',
                'Rata-rata Durasi',
                'Servis Ringan',
                'Servis Sedang',
                'Servis Berat'
            ]
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        return [
            $this->rowNumber,
            $row->nama_karyawan,
            $row->total_servis,
            $this->formatDurasi($row->total_durasi_menit),
            $this->formatDurasi($row->avg_durasi_menit),
            $row->servis_ringan,
            $row->servis_sedang,
            $row->servis_berat,
        ];
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
            // Ringkasan
            8 => [
                'font' => ['size' => 11],
                'alignment' => ['horizontal' => 'left']
            ],
            // Style untuk table header - warna biru #3b82f6
            10 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ],
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
                $event->sheet->mergeCells('A8:H8');

                // Set lebar kolom
                $columnWidths = [
                    'A' => 8,   // No
                    'B' => 25,  // Nama Mekanik
                    'C' => 15,  // Total Servis
                    'D' => 15,  // Total Durasi
                    'E' => 18,  // Rata-rata Durasi
                    'F' => 15,  // Ringan
                    'G' => 15,  // Sedang
                    'H' => 15,  // Berat
                ];

                foreach ($columnWidths as $column => $width) {
                    $event->sheet->getColumnDimension($column)->setWidth($width);
                }

                // Set tinggi row untuk header tabel
                $event->sheet->getRowDimension(10)->setRowHeight(25);

                // Style untuk data rows
                $dataStartRow = 11;
                $dataEndRow = $this->data->count() + 10;
                
                // Apply border dan alignment untuk data
                $event->sheet->getStyle("A{$dataStartRow}:H{$dataEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center'
                    ]
                ]);
                
                // Nama mekanik rata kiri
                $event->sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")->getAlignment()->setHorizontal('left');

                // Tambahkan total row di akhir
                $totalRow = $dataEndRow + 1;
                $event->sheet->setCellValue("A{$totalRow}", "Total Mekanik: " . $this->data->count());
                $event->sheet->mergeCells("A{$totalRow}:H{$totalRow}");
                $event->sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2F2F2']],
                    'alignment' => ['horizontal' => 'center'],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Timestamp
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
        return 'Kinerja Mekanik';
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
