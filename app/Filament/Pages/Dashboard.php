<?php
// app/Filament/Pages/Dashboard.php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\LaporanStatsOverview;
use App\Filament\Widgets\JenisLayananChart;
use App\Filament\Widgets\AntreanHarianChart;
use App\Filament\Widgets\DateTimeNow;

class Dashboard extends BaseDashboard
{
    /**
     * Metode ini secara spesifik HANYA mengatur widget
     * yang akan tampil di halaman Dashboard.
     *
     * Kita TIDAK memasukkan LaporanAntreanSelesai di sini.
     */
    public function getWidgets(): array
    {
        return [
            DateTimeNow::class,
            LaporanStatsOverview::class,
            JenisLayananChart::class,
            AntreanHarianChart::class,

        ];
    }
}