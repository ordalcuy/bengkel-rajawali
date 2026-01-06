<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Antrean;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AntreanHarianChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Antrean Harian (7 Hari Terakhir)';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        // --- PERUBAHAN 1: Atur bahasa ke Indonesia ---
        Carbon::setLocale('id');

        // 1. Ambil data dari database
        $data = Antrean::query()
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal') // Ambil data sebagai array [tanggal => total]
            ->all();

        // 2. Siapkan wadah untuk 7 hari terakhir
        $labels = [];
        $values = [];

        // 3. Loop selama 7 hari dari 6 hari yang lalu sampai hari ini
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString(); // Format Y-m-d

            // --- PERUBAHAN 2: Format hari ke Bahasa Indonesia ---
            $labels[] = $date->translatedFormat('l'); // 'l' untuk nama hari lengkap (Senin, Selasa, dst.)

            // Cek apakah ada data untuk tanggal ini, jika tidak, nilainya 0
            $values[] = $data[$dateString] ?? 0;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Total Antrean',
                    'data' => $values,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#1D4ED8',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'stepSize' => 25,
                    ],
                ],
            ],
        ];
    }
}