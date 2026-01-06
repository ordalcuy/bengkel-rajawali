<?php
// app/Filament/Widgets/JenisLayananChart.php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Pastikan ini ada

class JenisLayananChart extends ChartWidget
{
    protected static ?string $heading = 'Komposisi Jenis Layanan (Bulan Ini)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // [FIX] Menggunakan Query Builder untuk kontrol penuh
        $data = DB::table('antrean')
            ->where('antrean.status', '=', 'Selesai')
            ->whereMonth('antrean.created_at', Carbon::now()->month)
            ->whereYear('antrean.created_at', Carbon::now()->year)
            ->join('antrean_layanan', 'antrean.id', '=', 'antrean_layanan.antrean_id')
            ->join('layanan', 'antrean_layanan.layanan_id', '=', 'layanan.id')
            ->select('layanan.jenis_layanan', DB::raw('count(DISTINCT antrean.id) as total'))
            ->groupBy('layanan.jenis_layanan')
            ->get(); // Hasilnya adalah koleksi stdClass, bersih dan sederhana

        // Gunakan pluck yang sekarang dijamin berhasil pada koleksi sederhana ini
        $labels = $data->pluck('jenis_layanan')->map(fn ($jenis) => ucfirst($jenis))->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Antrean',
                    'data' => $values,
                    'backgroundColor' => [
                        '#EF4444', // Merah (Berat)
                        '#FFC107', // Kuning (Ringan)
                        '#3B82F6', // Biru (Sedang)
                    ],
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'display' => false,
                ],
                'x' => [
                    'display' => false,
                ],
            ],
        ];
    }
}