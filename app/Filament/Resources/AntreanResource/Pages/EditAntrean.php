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

    // Property untuk toggle jenis layanan
    public $jenisLayananString = '';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Set initial jenisLayananString dari jenis layanan yang sudah dipilih
        $antrean = $this->getRecord();
        $selectedLayanans = $antrean->layanan->pluck('jenis_layanan')->unique()->toArray();
        $this->jenisLayananString = implode(',', $selectedLayanans);

        return $data;
    }

    // Method toggle jenis layanan
    public function toggleJenisLayanan(string $jenis): void
    {
        $currentValue = $this->jenisLayananString;
        $values = $currentValue ? explode(',', $currentValue) : [];
        
        if (in_array($jenis, $values)) {
            $values = array_filter($values, fn($v) => $v !== $jenis);
        } else {
            $values[] = $jenis;
        }
        
        $this->jenisLayananString = implode(',', array_filter($values));
        
        // Berikan feedback visual
        $this->dispatch('layanan-toggled', jenis: $jenis, selected: in_array($jenis, $values));
    }

    // Helper method untuk cek apakah ada layanan untuk jenis tertentu
    public function hasLayananForJenis($jenisKendaraanId, $jenisLayanan)
    {
        // Jika jenis kendaraan tidak ada, tampilkan semua layanan untuk jenis tersebut
        if (!$jenisKendaraanId) {
            return Layanan::query()
                ->where('jenis_layanan', $jenisLayanan)
                ->exists();
        }
        
        return Layanan::query()
            ->where('jenis_layanan', $jenisLayanan)
            ->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId)
            ->exists();
    }

    // Helper method untuk get layanan by jenis
    public function getLayananForJenis($jenisKendaraanId, $jenisLayanan)
    {
        // Jika jenis kendaraan tidak ada, ambil semua layanan untuk jenis tersebut
        if (!$jenisKendaraanId) {
            return Layanan::query()
                ->where('jenis_layanan', $jenisLayanan)
                ->get();
        }
        
        return Layanan::query()
            ->where('jenis_layanan', $jenisLayanan)
            ->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId)
            ->get();
    }

    protected function handleRecordUpdate($record, array $data): Antrean
    {
        // Ambil SEMUA layanan berdasarkan jenis layanan yang dipilih
        $selectedJenisLayanan = !empty($this->jenisLayananString) ? explode(',', $this->jenisLayananString) : [];
        $allLayananIds = [];

        if (!empty($selectedJenisLayanan)) {
            $jenisKendaraanId = $record->kendaraan->jenis_kendaraan_id;

            // Jika jenis kendaraan tidak ada, ambil semua layanan tanpa filter
            if (!$jenisKendaraanId) {
                foreach ($selectedJenisLayanan as $jenis) {
                    $layananIds = Layanan::query()
                        ->where('jenis_layanan', $jenis)
                        ->pluck('id')
                        ->toArray();
                    
                    $allLayananIds = array_merge($allLayananIds, $layananIds);
                }
            } else {
                // Ambil SEMUA layanan untuk setiap jenis yang dipilih dengan filter jenis kendaraan
                foreach ($selectedJenisLayanan as $jenis) {
                    $layananIds = Layanan::query()
                        ->where('jenis_layanan', $jenis)
                        ->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId)
                        ->pluck('id')
                        ->toArray();
                    
                    $allLayananIds = array_merge($allLayananIds, $layananIds);
                }
            }

            // Hapus duplikat
            $allLayananIds = array_unique($allLayananIds);
        }

        // Update layanan - attach SEMUA layanan dari jenis yang dipilih
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

    protected function beforeSave(): void
    {
        // Validasi sebelum save
        if (empty($this->jenisLayananString)) {
            Notification::make()
                ->warning()
                ->title('Peringatan')
                ->body('Silakan pilih minimal satu jenis layanan sebelum menyimpan.')
                ->persistent()
                ->send();
            
            $this->halt();
        }
    }
}