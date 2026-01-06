<?php

namespace App\Filament\Widgets;

use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KinerjaMekanikStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        // Total mekanik aktif
        $totalMekanik = Karyawan::whereIn('role', ['mekanik', 'helper'])
            ->where('status', 'aktif')
            ->count();
        
        // Total servis selesai hari ini
        $totalServisHariIni = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->count();
        
        // Mekanik dengan servis terbanyak hari ini
        $topMekanik = Karyawan::whereIn('role', ['mekanik', 'helper'])
            ->withCount(['antreans' => function ($query) use ($today) {
                $query->where('status', 'Selesai')
                    ->whereDate('waktu_selesai', $today);
            }])
            ->orderByDesc('antreans_count')
            ->first();
        
        $topMekanikText = $topMekanik && $topMekanik->antreans_count > 0 
            ? $topMekanik->nama_karyawan . ' (' . $topMekanik->antreans_count . ')' 
            : '-';
        
        // Rata-rata servis per mekanik hari ini
        $avgServisPerMekanik = $totalMekanik > 0 
            ? round($totalServisHariIni / $totalMekanik, 1) 
            : 0;
        
        // Rata-rata durasi servis hari ini (semua mekanik)
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
        
        // Distribusi jenis layanan hari ini
        $ringan = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'ringan'))
            ->count();
        $sedang = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'sedang'))
            ->count();
        $berat = Antrean::where('status', 'Selesai')
            ->whereDate('waktu_selesai', $today)
            ->whereHas('layanan', fn($q) => $q->where('jenis_layanan', 'berat'))
            ->count();
        
        return [
            Stat::make('Mekanik Aktif', $totalMekanik)
                ->description('Total mekanik bertugas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('Servis Selesai Hari Ini', $totalServisHariIni)
                ->description('Total servis tuntas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Top Mekanik Hari Ini', $topMekanikText)
                ->description('Servis terbanyak')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
            
            Stat::make('Rata-rata/Mekanik', $avgServisPerMekanik)
                ->description('Servis per mekanik')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
            
            Stat::make('Rata-rata Durasi', $avgDurasiText)
                ->description('Waktu pengerjaan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
            
            Stat::make('Distribusi Layanan', "R:{$ringan} S:{$sedang} B:{$berat}")
                ->description('Ringan / Sedang / Berat')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('gray'),
        ];
    }
}
