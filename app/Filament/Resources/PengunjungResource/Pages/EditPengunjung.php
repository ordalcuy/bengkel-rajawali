<?php

namespace App\Filament\Resources\PengunjungResource\Pages;

use App\Filament\Resources\PengunjungResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengunjung extends EditRecord
{
    protected static string $resource = PengunjungResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Hapus Pengunjung')
                ->modalDescription('Apakah Anda yakin ingin menghapus pengunjung ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }
}
