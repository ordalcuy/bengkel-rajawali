<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\LaporanStatsOverview;
use App\Filament\Widgets\LaporanAntreanSelesai;

class Laporan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string $view = 'filament.pages.laporan';

    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Antrean';
    protected static ?string $title = 'Laporan Antrean';
    protected static ?int $navigationSort = 1;

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
            LaporanStatsOverview::class,
        ];
    }

    /**
     * Footer widgets - Table
     */
    public function getFooterWidgets(): array
    {
        return [
            LaporanAntreanSelesai::class,
        ];
    }
}