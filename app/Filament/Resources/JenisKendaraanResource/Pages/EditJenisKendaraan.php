<?php

namespace App\Filament\Resources\JenisKendaraanResource\Pages;

use App\Filament\Resources\JenisKendaraanResource;
use App\Models\Layanan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditJenisKendaraan extends EditRecord
{
    protected static string $resource = JenisKendaraanResource::class;

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
                ->modalHeading('Konfirmasi Hapus Jenis Kendaraan')
                ->modalDescription('Apakah Anda yakin ingin menghapus jenis kendaraan ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal'),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->form->getState();
        
        // Ambil data dari field yang benar
        $selectedJenisLayanans = json_decode($data['allowed_jenis_layanans_to_save'] ?? '[]', true) ?? [];
        
        DB::transaction(function () use ($record, $selectedJenisLayanans) {
            $jenisKendaraanId = (int) $record->id;
            $allLayanans = Layanan::all();

            foreach ($allLayanans as $layanan) {
                $shouldHaveAccess = in_array($layanan->jenis_layanan, $selectedJenisLayanans);
                
                // Pastikan jenis_kendaraan_akses adalah array
                $currentAkses = $layanan->jenis_kendaraan_akses ?? [];
                $currentAkses = is_array($currentAkses) ? $currentAkses : [];
                $currentlyHasAccess = in_array($jenisKendaraanId, $currentAkses);

                if ($shouldHaveAccess && !$currentlyHasAccess) {
                    // Tambah akses
                    $currentAkses[] = $jenisKendaraanId;
                    $layanan->jenis_kendaraan_akses = array_values(array_unique($currentAkses));
                    $layanan->save();
                    
                    \Log::info("Added access for jenis kendaraan {$jenisKendaraanId} to layanan {$layanan->id}");
                } elseif (!$shouldHaveAccess && $currentlyHasAccess) {
                    // Hapus akses
                    $currentAkses = array_filter($currentAkses, fn($id) => $id !== $jenisKendaraanId);
                    $layanan->jenis_kendaraan_akses = array_values($currentAkses);
                    $layanan->save();
                    
                    \Log::info("Removed access for jenis kendaraan {$jenisKendaraanId} from layanan {$layanan->id}");
                }
            }
        });
    }
}