<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\KinerjaMekanikWidget;
use App\Filament\Widgets\KinerjaMekanikStatsWidget;

class LaporanKinerjaMekanik extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.laporan-kinerja-mekanik';

    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Kinerja Mekanik';
    protected static ?string $title = 'Laporan Kinerja Mekanik';
    protected static ?int $navigationSort = 2;

    /**
     * Hanya role 'owner' yang bisa akses.
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('owner');
    }

    /**
     * Header widgets - Stats overview
     */
    public function getHeaderWidgets(): array
    {
        return [
            KinerjaMekanikStatsWidget::class,
        ];
    }

    /**
     * Footer widgets - Kinerja Mekanik Table
     */
    public function getFooterWidgets(): array
    {
        return [
            KinerjaMekanikWidget::class,
        ];
    }
}
