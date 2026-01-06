<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\JenisKendaraan;
use App\Models\Layanan;
use App\Models\Pengunjung;
use App\Models\Kendaraan;
use App\Models\Antrean;
use App\Enums\MerkKendaraan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. CLEANUP OLD TEST DATA (AMAN - hanya hapus data test)
        $this->cleanupOldTestData();

        // 1. BUAT ROLES
        $roleKasir = Role::firstOrCreate(['name' => 'kasir']);
        $roleOwner = Role::firstOrCreate(['name' => 'owner']);

        // 2. BUAT USERS
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@bengkel.com'],
            ['name' => 'Kasir', 'password' => Hash::make('password')]
        );
        $kasir->assignRole($roleKasir);

        $owner = User::firstOrCreate(
            ['email' => 'owner@bengkel.com'],
            ['name' => 'Owner', 'password' => Hash::make('password')]
        );
        $owner->assignRole($roleOwner);

        // 3. BUAT KARYAWAN (5 MEKANIK + 3 HELPER)
        $mekanik1 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Budi Santoso'],
            ['role' => 'mekanik', 'alamat' => 'Jl. Veteran No. 1, Malang', 'no_tlp' => '081111111111']
        );
        $mekanik2 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Agus Setiawan'],
            ['role' => 'mekanik', 'alamat' => 'Jl. Diponegoro No. 5, Malang', 'no_tlp' => '082222222222']
        );
        $mekanik3 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Rizki Pratama'],
            ['role' => 'mekanik', 'alamat' => 'Jl. Soekarno Hatta No. 12, Malang', 'no_tlp' => '083333333333']
        );
        $mekanik4 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Dedi Kurniawan'],
            ['role' => 'mekanik', 'alamat' => 'Jl. Basuki Rahmat No. 8, Malang', 'no_tlp' => '084444444444']
        );
        $mekanik5 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Fajar Nugroho'],
            ['role' => 'mekanik', 'alamat' => 'Jl. Jakarta No. 15, Malang', 'no_tlp' => '085555555555']
        );

        $helper1 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Candra Wijaya'],
            ['role' => 'helper', 'alamat' => 'Jl. Ijen No. 7, Malang', 'no_tlp' => '086666666666']
        );
        $helper2 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Eko Prasetyo'],
            ['role' => 'helper', 'alamat' => 'Jl. Kawi No. 3, Malang', 'no_tlp' => '087777777777']
        );
        $helper3 = Karyawan::firstOrCreate(
            ['nama_karyawan' => 'Hendro Wibowo'],
            ['role' => 'helper', 'alamat' => 'Jl. Semeru No. 9, Malang', 'no_tlp' => '088888888888']
        );

        // 4. JENIS KENDARAAN
        $matic = JenisKendaraan::firstOrCreate(['nama_jenis' => 'Matic']);
        $bebek = JenisKendaraan::firstOrCreate(['nama_jenis' => 'Bebek']);
        $sportKecil = JenisKendaraan::firstOrCreate(['nama_jenis' => 'Sport <250cc']);
        $sportBesar = JenisKendaraan::firstOrCreate(['nama_jenis' => 'Sport >250cc']);

        // 5. LAYANAN
        $ringanAksesIds = [$matic->id, $bebek->id, $sportKecil->id, $sportBesar->id];
        $sedangAksesIds = [$matic->id, $bebek->id, $sportKecil->id];
        $beratAksesIds = [$matic->id, $bebek->id];

        // Layanan Ringan
        $layananRingan1 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Ganti Oli & Filter'],
            [
                'jenis_layanan' => 'ringan',
                'jenis_kendaraan_akses' => json_encode($ringanAksesIds)
            ]
        );
        $layananRingan2 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Cek Tekanan Angin & Pelumasan Rantai'],
            [
                'jenis_layanan' => 'ringan',
                'jenis_kendaraan_akses' => json_encode($ringanAksesIds)
            ]
        );
        $layananRingan3 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Pembersihan Injektor (Ringan)'],
            [
                'jenis_layanan' => 'ringan',
                'jenis_kendaraan_akses' => json_encode($ringanAksesIds)
            ]
        );
        $layananRingan4 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Servis Aki & Sistem Pengisian'],
            [
                'jenis_layanan' => 'ringan',
                'jenis_kendaraan_akses' => json_encode($ringanAksesIds)
            ]
        );
        $layananRingan5 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Pembersihan & Pelumasan Rantai'],
            [
                'jenis_layanan' => 'ringan',
                'jenis_kendaraan_akses' => json_encode($ringanAksesIds)
            ]
        );

        // Layanan Sedang
        $layananSedang1 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Servis Tune Up Mesin'],
            [
                'jenis_layanan' => 'sedang',
                'jenis_kendaraan_akses' => json_encode($sedangAksesIds)
            ]
        );
        $layananSedang2 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Ganti Kampas Rem Depan & Belakang'],
            [
                'jenis_layanan' => 'sedang',
                'jenis_kendaraan_akses' => json_encode($sedangAksesIds)
            ]
        );
        $layananSedang3 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Pembersihan Karburator / Throttle Body'],
            [
                'jenis_layanan' => 'sedang',
                'jenis_kendaraan_akses' => json_encode($sedangAksesIds)
            ]
        );
        $layananSedang4 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Servis Sistem Pendingin'],
            [
                'jenis_layanan' => 'sedang',
                'jenis_kendaraan_akses' => json_encode($sedangAksesIds)
            ]
        );
        $layananSedang5 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Ganti Busi & Tune Up'],
            [
                'jenis_layanan' => 'sedang',
                'jenis_kendaraan_akses' => json_encode($sedangAksesIds)
            ]
        );

        // Layanan Berat
        $layananBerat1 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Turun Mesin / Overhaul'],
            [
                'jenis_layanan' => 'berat',
                'jenis_kendaraan_akses' => json_encode($beratAksesIds)
            ]
        );
        $layananBerat2 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Perbaikan Kelistrikan Total'],
            [
                'jenis_layanan' => 'berat',
                'jenis_kendaraan_akses' => json_encode($beratAksesIds)
            ]
        );
        $layananBerat3 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Overhaul Transmisi'],
            [
                'jenis_layanan' => 'berat',
                'jenis_kendaraan_akses' => json_encode($beratAksesIds)
            ]
        );
        $layananBerat4 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Perbaikan Sistem Bahan Bakar Lengkap'],
            [
                'jenis_layanan' => 'berat',
                'jenis_kendaraan_akses' => json_encode($beratAksesIds)
            ]
        );
        $layananBerat5 = Layanan::firstOrCreate(
            ['nama_layanan' => 'Rebuild Mesin Lengkap'],
            [
                'jenis_layanan' => 'berat',
                'jenis_kendaraan_akses' => json_encode($beratAksesIds)
            ]
        );

        $allLayanans = [
            $layananRingan1, $layananRingan2, $layananRingan3, $layananRingan4, $layananRingan5,
            $layananSedang1, $layananSedang2, $layananSedang3, $layananSedang4, $layananSedang5,
            $layananBerat1, $layananBerat2, $layananBerat3, $layananBerat4, $layananBerat5
        ];

        // 6. 10 PENGUNJUNG AKTIF + KENDARAAN
        $pengunjungs = [];
        $allKendaraans = [];

        // Data 10 pelanggan aktif
        $pelangganData = [
            ['Andi Pratama', '081234567890', 'Jl. Merdeka No. 10, Malang', 'N 1234 AB', MerkKendaraan::HONDA->value, $matic],
            ['Siti Aminah', '082345678901', 'Jl. Sudirman No. 25, Batu', 'N 5678 CD', MerkKendaraan::YAMAHA->value, $bebek],
            ['Budi Santoso', '083456789012', 'Jl. Panglima Sudirman No. 15, Malang', 'N 9012 EF', MerkKendaraan::SUZUKI->value, $sportKecil],
            ['Rina Wijaya', '084567890123', 'Jl. Jakarta No. 8, Malang', 'B 1234 GH', MerkKendaraan::KAWASAKI->value, $sportBesar],
            ['Ahmad Fauzi', '085678901234', 'Jl. Bromo No. 3, Malang', 'N 3456 IJ', MerkKendaraan::HONDA->value, $matic],
            ['Dewi Lestari', '086789012345', 'Jl. Kawi No. 12, Malang', 'N 7890 KL', MerkKendaraan::YAMAHA->value, $bebek],
            ['Joko Prasetyo', '087890123456', 'Jl. Semeru No. 7, Batu', 'N 1111 MN', MerkKendaraan::HONDA->value, $sportKecil],
            ['Maya Sari', '088901234567', 'Jl. Arjuna No. 5, Malang', 'N 2222 OP', MerkKendaraan::SUZUKI->value, $matic],
            ['Rizky Ramadhan', '089012345678', 'Jl. Brawijaya No. 20, Malang', 'B 3333 QR', MerkKendaraan::KAWASAKI->value, $sportBesar],
            ['Linda Hartati', '089123456789', 'Jl. Gajah Mada No. 30, Batu', 'N 4444 ST', MerkKendaraan::YAMAHA->value, $bebek]
        ];

        foreach ($pelangganData as $index => $data) {
            $pengunjung = Pengunjung::firstOrCreate(
                ['nama_pengunjung' => $data[0]],
                ['nomor_tlp' => $data[1], 'alamat' => $data[2]]
            );
            $pengunjungs[$index + 1] = $pengunjung;

            $kendaraan = Kendaraan::firstOrCreate(
                ['nomor_plat' => $data[3]],
                [
                    'pengunjung_id' => $pengunjung->id,
                    'merk' => $data[4],
                    'jenis_kendaraan_id' => $data[5]->id
                ]
            );
            $allKendaraans[] = $kendaraan;
        }

        // 7. GENERATE DATA ANTREAN UNTUK TESTING SISTEM BARU
        $this->generateTestData(
            [$mekanik1, $mekanik2, $mekanik3, $mekanik4, $mekanik5],
            [$helper1, $helper2, $helper3],
            $allKendaraans,
            $pengunjungs,
            $allLayanans
        );

        $this->command->info("✅ Test data berhasil dibuat!");
        $this->command->info("👤 Login kasir: kasir@bengkel.com / password");
        $this->command->info("👤 Login owner: owner@bengkel.com / password");
    }

    /**
     * Cleanup old test data (safe deletion - only test data)
     */
    private function cleanupOldTestData(): void
    {
        $this->command->info("🗑️  Cleaning up old test data...");
        
        // Delete Antrean (cascade will handle antrean_layanan pivot)
        $deletedAntrean = Antrean::query()->delete();
        $this->command->info("   - Deleted {$deletedAntrean} antrean records");
        
        // Delete Kendaraan
        $deletedKendaraan = Kendaraan::query()->delete();
        $this->command->info("   - Deleted {$deletedKendaraan} kendaraan records");
        
        // Delete Pengunjung
        $deletedPengunjung = Pengunjung::query()->delete();
        $this->command->info("   - Deleted {$deletedPengunjung} pengunjung records");
        
        $this->command->info("✅ Cleanup completed!\n");
    }

    private function generateTestData($mekaniks, $helpers, $kendaraans, $pengunjungs, $layanans)
    {
        $today = Carbon::today();
        $allKaryawans = array_merge($mekaniks, $helpers);

        // 1. GENERATE HISTORY NOVEMBER (Full Month)
        $this->createNovemberHistory($kendaraans, $pengunjungs, $allKaryawans, $layanans);

        // 2. GENERATE TODAY'S DATA (10 items)
        $this->createTodaysAntrean($kendaraans, $pengunjungs, $mekaniks, $helpers, $layanans, $today);
    }

    private function createNovemberHistory($kendaraans, $pengunjungs, $allKaryawans, $layanans)
    {
        // November from 1st to 30th
        $startDate = Carbon::createFromDate(null, 11, 1)->startOfDay();
        $endDate = Carbon::createFromDate(null, 11, 30)->endOfDay();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Randomly generate 5-15 antreans per day
            $dailyCount = rand(5, 15);

            for ($i = 1; $i <= $dailyCount; $i++) {
                $kendaraanPilihan = $kendaraans[array_rand($kendaraans)];
                $karyawanPilihan = $allKaryawans[array_rand($allKaryawans)];
                
                // Working hours 08:00 - 16:00
                $jamMulai = $date->copy()->setTime(rand(8, 15), rand(0, 59));
                $durasiLayanan = rand(1, 4); 
                $waktuSelesai = $jamMulai->copy()->addHours($durasiLayanan);

                // Format: A001, A002, etc. unique per day
                $nomorAntrean = 'A' . str_pad($i, 3, '0', STR_PAD_LEFT);

                $antrean = Antrean::forceCreate([
                    'nomor_antrean' => $nomorAntrean,
                    'kendaraan_id' => $kendaraanPilihan->id,
                    'pengunjung_id' => $kendaraanPilihan->pengunjung_id, // Use direct ID from kendaraan
                    'karyawan_id' => $karyawanPilihan->id,
                    'status' => 'Selesai',
                    'waktu_mulai' => $jamMulai,
                    'waktu_selesai' => $waktuSelesai,
                    'created_at' => $jamMulai,
                    'updated_at' => $waktuSelesai
                ]);

                // Attach services
                $jumlahLayanan = rand(1, 3);
                $layananTerpilih = collect($layanans)->random($jumlahLayanan)->pluck('id')->toArray();
                $antrean->layanan()->sync($layananTerpilih);
            }
        }

        $this->command->info("✅ Created complete history for November (A00x format)");
    }

    private function createTodaysAntrean($kendaraans, $pengunjungs, $mekaniks, $helpers, $layanans, $today)
    {
        // Generate exactly 10 antreans for today
        for ($i = 1; $i <= 10; $i++) {
            $formattedNumber = 'A' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            // Mix of status: first 3 Dikerjakan, rest Menunggu
            $status = ($i <= 3) ? 'Dikerjakan' : 'Menunggu';
            $mekanik = ($status == 'Dikerjakan') ? $mekaniks[$i-1] : null; // Assign first few mechanics
            
            // Time staggering
            $jamMasuk = $today->copy()->setTime(8, 0)->addMinutes(($i - 1) * 20); // Interval 20 mins
            $waktuMulai = ($status == 'Dikerjakan') ? $jamMasuk->copy()->addMinutes(5) : null;

            // Pick vehicle cyclically or random
            $kendaraan = $kendaraans[($i - 1) % count($kendaraans)];

            $antrean = Antrean::forceCreate([
                'nomor_antrean' => $formattedNumber,
                'kendaraan_id' => $kendaraan->id,
                'pengunjung_id' => $kendaraan->pengunjung_id, // Use direct ID from kendaraan
                'karyawan_id' => $mekanik ? $mekanik->id : null,
                'status' => $status,
                'waktu_mulai' => $waktuMulai,
                'waktu_selesai' => null, // Not finished yet
                'created_at' => $jamMasuk,
                'updated_at' => $jamMasuk
            ]);

            // Random services
            $jumlahLayanan = rand(1, 2);
            $layananTerpilih = collect($layanans)->random($jumlahLayanan)->pluck('id')->toArray();
            $antrean->layanan()->sync($layananTerpilih);
        }

        $this->command->info("✅ Created 10 antreans for TODAY (A001 - A010)");
    }
}