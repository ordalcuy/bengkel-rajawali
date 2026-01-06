<?php

namespace App\Filament\Resources\RiwayatAntreanResource\Pages;

use App\Filament\Resources\RiwayatAntreanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiwayatAntreans extends ListRecords
{
    protected static string $resource = RiwayatAntreanResource::class;

    protected static ?string $title = 'Riwayat Antrean';

    protected function getHeaderActions(): array
    {
        return [
            // Tambahkan actions header jika diperlukan
        ];
    }

    protected function getTableFiltersFormHeading(): ?string
    {
        return 'Filter';
    }
}