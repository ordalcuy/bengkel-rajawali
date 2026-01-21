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

    // Property untuk toggle jenis layanan
    public $jenisLayananString = '';

    protected function handleRecordCreation(array $data): Antrean
    {
        return DB::transaction(function () use ($data) {
            // =====================================
            // VALIDASI INPUT
            // =====================================
            
            // Validasi nama pelanggan
            if (empty($data['nama_pengunjung'])) {
                Notification::make()
                    ->danger()
                    ->title('❌ Validasi Gagal')
                    ->body('Nama pelanggan wajib diisi!')
                    ->persistent()
                    ->send();
                throw new \Illuminate\Validation\ValidationException(
                    validator([], ['nama_pengunjung' => 'required'])
                );
            }

            // Validasi jenis kendaraan
            if (empty($data['jenis_kendaraan_id']) && empty($data['kendaraan_id'])) {
                Notification::make()
                    ->danger()
                    ->title('❌ Validasi Gagal')
                    ->body('Jenis kendaraan wajib dipilih!')
                    ->persistent()
                    ->send();
                throw new \Illuminate\Validation\ValidationException(
                    validator([], ['jenis_kendaraan_id' => 'required'])
                );
            }

            // Ambil jenis layanan (opsional)
            $jenisLayananRaw = $data['jenis_layanan'] ?? $this->jenisLayananString ?? '';

            // =====================================
            // 1. HANDLE PELANGGAN
            // =====================================
            $pengunjung = null;
            
            if (!empty($data['pengunjung_id'])) {
                $pelangganLama = Pengunjung::find($data['pengunjung_id']);
                
                if ($pelangganLama) {
                    $dataChanged = (
                        $pelangganLama->nama_pengunjung !== $data['nama_pengunjung'] ||
                        $pelangganLama->nomor_tlp !== $data['nomor_tlp'] ||
                        ($data['alamat'] ?? '') !== ($pelangganLama->alamat ?? '')
                    );
                    
                    $pengunjung = $dataChanged 
                        ? Pengunjung::create([
                            'nama_pengunjung' => $data['nama_pengunjung'],
                            'nomor_tlp' => $data['nomor_tlp'],
                            'alamat' => $data['alamat'] ?? null,
                        ])
                        : $pelangganLama;
                }
            }
            
            if (!$pengunjung) {
                $pengunjung = Pengunjung::create([
                    'nama_pengunjung' => $data['nama_pengunjung'],
                    'nomor_tlp' => $data['nomor_tlp'],
                    'alamat' => $data['alamat'] ?? null,
                ]);
            }

            // =====================================
            // 2. HANDLE KENDARAAN
            // =====================================
            $kendaraan = !empty($data['kendaraan_id']) 
                ? Kendaraan::find($data['kendaraan_id']) 
                : null;
            
            if (!$kendaraan) {
                try {
                    $kendaraan = Kendaraan::create([
                        'pengunjung_id' => $pengunjung->id,
                        'nomor_plat' => $data['nomor_plat'] ?? 'TP-' . date('ymd') . '-' . time(),
                        'merk' => $data['merk'] ?? null,
                        'jenis_kendaraan_id' => $data['jenis_kendaraan_id'],
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        Notification::make()
                            ->danger()
                            ->title('❌ Plat Nomor Sudah Terdaftar')
                            ->body('Plat nomor "' . ($data['nomor_plat'] ?? '') . '" sudah terdaftar. Gunakan plat nomor yang berbeda atau kosongkan.')
                            ->persistent()
                            ->send();
                        throw new \Illuminate\Validation\ValidationException(
                            validator([], ['nomor_plat' => 'unique'])
                        );
                    }
                    throw $e;
                }
            }

            // =====================================
            // 3. AMBIL LAYANAN (OPSIONAL)
            // =====================================
            $allLayananIds = [];
            
            if (!empty($jenisLayananRaw)) {
                foreach (explode(',', $jenisLayananRaw) as $jenis) {
                    if (empty(trim($jenis))) continue;
                    
                    $query = Layanan::where('jenis_layanan', trim($jenis));
                    
                    if ($kendaraan->jenis_kendaraan_id) {
                        $query->whereJsonContains('jenis_kendaraan_akses', (int) $kendaraan->jenis_kendaraan_id);
                    }
                    
                    $allLayananIds = array_merge($allLayananIds, $query->pluck('id')->toArray());
                }
                $allLayananIds = array_unique($allLayananIds);
            }

            // =====================================
            // 4. BUAT ANTREAN
            // =====================================
            $antrean = Antrean::create([
                'kendaraan_id' => $kendaraan->id,
                'pengunjung_id' => $pengunjung->id,
                'status' => 'Menunggu',
            ]);

            if (!empty($allLayananIds)) {
                $antrean->layanan()->attach($allLayananIds);
            }

            if (!$antrean->nomor_antrean) {
                $antrean->generateNomorAntrean();
                $antrean->save();
            }

            return $antrean;
        });
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
        $data = $this->form->getState();
        
        // Skip jika nomor plat kosong atau sudah ada kendaraan_id
        if (empty($data['nomor_plat']) || !empty($data['kendaraan_id'])) {
            return;
        }
        
        // Cek duplikasi plat nomor
        $existingKendaraan = Kendaraan::where('nomor_plat', $data['nomor_plat'])->first();
        
        if ($existingKendaraan) {
            $namaOwner = $existingKendaraan->pengunjung?->nama_pengunjung ?? 'Tidak diketahui';
            
            Notification::make()
                ->warning()
                ->title('⚠️ Plat Nomor Sudah Terdaftar')
                ->body('Plat nomor "' . $data['nomor_plat'] . '" sudah terdaftar atas nama ' . $namaOwner . '. Data kendaraan akan menggunakan yang sudah ada.')
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