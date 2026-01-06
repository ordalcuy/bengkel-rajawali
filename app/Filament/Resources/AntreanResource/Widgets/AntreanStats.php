<?php

namespace App\Filament\Resources\AntreanResource\Widgets;

use App\Models\Antrean;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AntreanStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalMenunggu = Antrean::where('status', 'Menunggu')->count();
        $totalDikerjakan = Antrean::where('status', 'Dikerjakan')->count();
        $totalHariIni = Antrean::whereDate('created_at', today())->count();

        return [
            Stat::make('Antrean Menunggu', $totalMenunggu)
                ->description('Belum ditugaskan mekanik')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, 5, 7]),

            Stat::make('Sedang Dikerjakan', $totalDikerjakan)
                ->description('Dalam proses servis')
                ->descriptionIcon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->chart([4, 2, 5, 3, 6, 4, 2, 3]),

            Stat::make('Total Hari Ini', $totalHariIni)
                ->description('Antrean baru hari ini')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('success')
                ->chart([2, 3, 4, 5, 6, 7, 8, 9]),
        ];
    }
}