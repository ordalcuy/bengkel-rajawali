<?php

namespace App\Filament\Resources\LayananResource\Pages;

use App\Filament\Resources\LayananResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLayanan extends CreateRecord
{
    protected static string $resource = LayananResource::class;

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
