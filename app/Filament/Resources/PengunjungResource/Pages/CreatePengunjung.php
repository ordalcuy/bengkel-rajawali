<?php

namespace App\Filament\Resources\PengunjungResource\Pages;

use App\Filament\Resources\PengunjungResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengunjung extends CreateRecord
{
    protected static string $resource = PengunjungResource::class;

        protected static ?string $title = 'Tambah Pengunjung Baru';

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
{
    return [
        \Filament\Actions\Action::make('create')
            ->label('Simpan') // ganti label sesuai kebutuhan
            ->submit('create') // trigger action "create"
            ->color('primary')
            ->button(),

        \Filament\Actions\Action::make('cancel')
            ->label('Batal')
            ->url($this->getResource()::getUrl('index'))
            ->color('secondary')
            ->button(),
    ];
}

}
