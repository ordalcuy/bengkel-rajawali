<?php

namespace App\Filament\Widgets;

use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon as SupportCarbon;

class LaporanStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // Full width for better display
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        // Statistik hari ini
        $totalAntrean = Antrean::whereDate('created_at', $today)->count();
        $antreanMenunggu = Antrean::where('status', 'Menunggu')->count();
        $antreanDikerjakan = Antrean::where('status', 'Dikerjakan')->count();
        $antreanSelesai = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->count();
        
        // Mekanik aktif
        $mekanikAktif = Karyawan::where('role', 'mekanik')
            ->where('status', 'aktif')
            ->count();
        
        // Rata-rata durasi servis hari ini (dalam menit)
        $avgDurasi = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->whereNotNull('waktu_mulai')
            ->whereNotNull('waktu_selesai')
            ->get()
            ->avg(function ($antrean) {
                $mulai = Carbon::parse($antrean->waktu_mulai);
                $selesai = Carbon::parse($antrean->waktu_selesai);
                return $mulai->diffInMinutes($selesai);
            });
        
        // Format durasi ke jam dan menit
        if ($avgDurasi) {
            $totalMenit = round($avgDurasi);
            $jam = floor($totalMenit / 60);
            $menit = $totalMenit % 60;
            
            if ($jam > 0) {
                $avgDurasiText = $jam . ' jam ' . $menit . ' menit';
            } else {
                $avgDurasiText = $menit . ' menit';
            }
        } else {
            $avgDurasiText = '-';
        }
        
        return [
            Stat::make('Total Antrean Hari Ini', $totalAntrean)
                ->description('Antrean masuk hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, $totalAntrean])
                ->extraAttributes([
                    'class' => 'cursor-pointer transition hover:scale-[1.02]',
                ]),
            
            Stat::make('Selesai Hari Ini', $antreanSelesai)
                ->description('Servis tuntas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer transition hover:scale-[1.02]',
                ]),
            
            Stat::make('Rata-rata Durasi', $avgDurasiText)
                ->description('Waktu servis')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer transition hover:scale-[1.02]',
                ]),
        ];
    }
}