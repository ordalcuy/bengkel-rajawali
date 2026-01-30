<?php

namespace App\Filament\Resources\AntreanResource\Pages;

use App\Filament\Resources\AntreanResource;
use App\Models\Antrean;
use App\Models\Layanan;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAntrean extends EditRecord
{
    protected static string $resource = AntreanResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-populate checkbox fields with existing layanan IDs
        $antrean = $this->getRecord();
        $layananIds = $antrean->layanan->pluck('id');
        
        // Group by jenis_layanan
        $data['layanan_ringan'] = $antrean->layanan->where('jenis_layanan', 'ringan')->pluck('id')->toArray();
        $data['layanan_sedang'] = $antrean->layanan->where('jenis_layanan', 'sedang')->pluck('id')->toArray();
        $data['layanan_berat'] = $antrean->layanan->where('jenis_layanan', 'berat')->pluck('id')->toArray();

        return $data;
    }



    protected function handleRecordUpdate($record, array $data): Antrean
    {
        // Collect all layanan IDs from checkbox fields
        $allLayananIds = [];
        
        if (!empty($data['layanan_ringan'])) {
            $allLayananIds = array_merge($allLayananIds, $data['layanan_ringan']);
        }
        if (!empty($data['layanan_sedang'])) {
            $allLayananIds = array_merge($allLayananIds, $data['layanan_sedang']);
        }
        if (!empty($data['layanan_berat'])) {
            $allLayananIds = array_merge($allLayananIds, $data['layanan_berat']);
        }
        
        // Remove duplicates
        $allLayananIds = array_unique($allLayananIds);

        // Sync layanan relationship
        $record->layanan()->sync($allLayananIds);

        return $record;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('✅ Data Antrean Berhasil Diperbarui')
            ->body('Perubahan pada data antrean telah berhasil disimpan.')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->seconds(4);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan')
                ->icon('heroicon-o-check'),
            $this->getCancelFormAction()
                ->label('Batal')
                ->color('gray')
                ->icon('heroicon-o-x-mark'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // beforeSave validation removed - layanan selection is now optional
}