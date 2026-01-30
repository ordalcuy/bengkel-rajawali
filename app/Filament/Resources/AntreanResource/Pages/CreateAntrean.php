<?php

namespace App\Filament\Resources\AntreanResource\Pages;

use App\Filament\Resources\AntreanResource;
use App\Models\Antrean;
use App\Models\Pengunjung;
use App\Models\Kendaraan;
use App\Models\Layanan;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateAntrean extends CreateRecord
{
    protected static string $resource = AntreanResource::class;

    public static ?string $title = 'Buat Antrean Baru';

    protected function handleRecordCreation(array $data): Antrean
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. Handle Pelanggan
                if (!empty($data['pengunjung_id'])) {
                    // Pelanggan existing terdeteksi dari auto-fill
                    $pelangganLama = Pengunjung::find($data['pengunjung_id']);
                    
                    // Cek apakah kasir mengubah data pelanggan
                    $dataChanged = (
                        $pelangganLama->nama_pengunjung !== $data['nama_pengunjung'] ||
                        $pelangganLama->nomor_tlp !== $data['nomor_tlp'] ||
                        ($data['alamat'] ?? '') !== ($pelangganLama->alamat ?? '')
                    );
                    
                    if ($dataChanged) {
                        // Data berubah → Buat pelanggan BARU
                        $pengunjung = Pengunjung::create([
                            'nama_pengunjung' => $data['nama_pengunjung'],
                            'nomor_tlp' => $data['nomor_tlp'],
                            'alamat' => $data['alamat'] ?? null,
                        ]);
                    } else {
                        // Data sama → Gunakan pelanggan lama
                        $pengunjung = $pelangganLama;
                    }
                } else {
                    // Pelanggan baru (tidak ada auto-detection)
                    $pengunjung = Pengunjung::create([
                        'nama_pengunjung' => $data['nama_pengunjung'],
                        'nomor_tlp' => $data['nomor_tlp'],
                        'alamat' => $data['alamat'] ?? null,
                    ]);
                }

                // 2. Handle Kendaraan
                if (!empty($data['kendaraan_id'])) {
                    // Kendaraan existing - TIDAK DI-CREATE ULANG
                    $kendaraan = Kendaraan::find($data['kendaraan_id']);
                    
                    // Kendaraan lama tetap terlink pada record-nya
                    // TIDAK ada update kendaraan
                } else {
                    // Kendaraan baru
                    $kendaraan = Kendaraan::create([
                        'pengunjung_id' => $pengunjung->id,
                        'nomor_plat' => $data['nomor_plat'] ?? 'TANPA-PLAT',
                        'merk' => $data['merk'] ?? null,
                        'jenis_kendaraan_id' => $data['jenis_kendaraan_id'],
                    ]);
                }

                // Validasi kendaraan dan pengunjung
                if (!$kendaraan) {
                    throw new \Exception('Kendaraan tidak ditemukan atau gagal dibuat');
                }

                if (!$pengunjung) {
                    throw new \Exception('Pelanggan tidak ditemukan atau gagal dibuat');
                }

                // 3. Ambil layanan IDs langsung dari checkbox yang dipilih
                $allLayananIds = [];
                
                // Collect from all checkbox groups
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

                // 4. Buat antrean
                $antrean = Antrean::create([
                    'kendaraan_id' => $kendaraan->id,
                    'pengunjung_id' => $pengunjung->id,
                    'status' => 'Menunggu',
                ]);

                // 5. Attach SEMUA layanan dari jenis yang dipilih
                if (!empty($allLayananIds)) {
                    $antrean->layanan()->attach($allLayananIds);
                }

                // 6. Generate nomor antrean
                if (!$antrean->nomor_antrean) {
                    $antrean->generateNomorAntrean();
                    $antrean->save();
                }

                return $antrean;

            } catch (\Exception $e) {
                \Log::error('Error creating antrean: ' . $e->getMessage());
                throw $e;
            }
        });
    }



    // Helper method untuk cek apakah ada layanan untuk jenis tertentu
    public function hasLayananForJenis($jenisKendaraanId, $jenisLayanan)
    {
        $query = Layanan::where('jenis_layanan', $jenisLayanan);
        
        if ($jenisKendaraanId) {
            $query->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId);
        }
        
        return $query->exists();
    }

    // Helper method untuk get layanan by jenis
    public function getLayananForJenis($jenisKendaraanId, $jenisLayanan)
    {
        $query = Layanan::where('jenis_layanan', $jenisLayanan);
        
        if ($jenisKendaraanId) {
            $query->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId);
        }
        
        return $query->get();
    }

    protected function getRedirectUrl(): string
    {
        // Redirect ke cetak struk setelah create
        return route('antrean.cetak', $this->record->id);
    }



    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('🎉 Antrean Berhasil Dibuat!')
            ->body('Antrean baru berhasil dibuat dan sedang dialihkan ke cetak struk.')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->seconds(5);
    }

    protected function beforeCreate(): void
    {
        // Validasi layanan

        
        // Validasi duplikasi plat (jika kendaraan baru)
        $data = $this->form->getState();
        
        if (empty($data['kendaraan_id'])) {
            $existingKendaraan = Kendaraan::where('nomor_plat', $data['nomor_plat'])->first();
            
            if ($existingKendaraan) {
                Notification::make()
                    ->warning()
                    ->title('Plat Nomor Sudah Terdaftar')
                    ->body('Plat nomor "' . $data['nomor_plat'] . '" sudah terdaftar atas nama ' . $existingKendaraan->pengunjung->nama_pengunjung . '. Data kendaraan akan menggunakan yang sudah ada.')
                    ->persistent()
                    ->send();
                
                // Auto-set ke kendaraan existing
                $this->form->fill([
                    'kendaraan_id' => $existingKendaraan->id,
                    'merk' => $existingKendaraan->merk,
                    'jenis_kendaraan_id' => $existingKendaraan->jenis_kendaraan_id,
                ]);
            }
        }

    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Simpan Antrean & Cetak Struk')
                ->submit('create')
                ->color('success')
                ->icon('heroicon-o-printer')
                ->button(),
            \Filament\Actions\Action::make('cancel')
                ->label('Batal')
                ->url(route('filament.admin.resources.antreans.index'))
                ->color('gray')
                ->icon('heroicon-o-x-mark')
                ->button(),
        ];
    }
}