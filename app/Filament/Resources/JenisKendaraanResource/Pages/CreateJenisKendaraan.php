<?php

namespace App\Filament\Resources\JenisKendaraanResource\Pages;

use App\Filament\Resources\JenisKendaraanResource;
use App\Models\Layanan;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateJenisKendaraan extends CreateRecord
{
    protected static string $resource = JenisKendaraanResource::class;

    protected static ?string $title = 'Buat Jenis Kendaraan Baru';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
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
                } elseif (!$shouldHaveAccess && $currentlyHasAccess) {
                    // Hapus akses
                    $currentAkses = array_filter($currentAkses, fn($id) => $id !== $jenisKendaraanId);
                    $layanan->jenis_kendaraan_akses = array_values($currentAkses);
                    $layanan->save();
                }
            }
        });
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Simpan')
                ->submit('create')
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