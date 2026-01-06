<?php

namespace App\Filament\Resources\JenisKendaraanResource\Pages;

use App\Filament\Resources\JenisKendaraanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisKendaraans extends ListRecords
{
    protected static string $resource = JenisKendaraanResource::class;
        protected static ?string $title = 'Jenis Kendaraan';


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Jenis Kendaraan'),
        ];
    }
}
