<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKaryawan extends CreateRecord
{
    protected static string $resource = KaryawanResource::class;

        protected static ?string $title = 'Tambah Karyawan Baru';


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
