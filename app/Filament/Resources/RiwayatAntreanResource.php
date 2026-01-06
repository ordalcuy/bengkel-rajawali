<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatAntreanResource\Pages;
use App\Filament\Resources\RiwayatAntreanResource\Pages\AntreLagiPage;
use App\Models\Antrean;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Kendaraan;
use App\Models\JenisKendaraan;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Radio;
use Livewire\Component;
use App\Enums\MerkKendaraan;
use Filament\Forms\Components\Card;

class RiwayatAntreanResource extends Resource
{
    protected static ?string $model = Antrean::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Riwayat Antrean';

    protected static ?string $navigationGroup = 'Manajemen Antrean';

    protected static ?string $label = 'Riwayat Antrean';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('kasir');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form schema untuk riwayat (bisa dikosongkan atau disesuaikan)
            ]);
    }

    /**
     * Helper method to get jenis_kendaraan_id based on form state
     */
    private static function getJenisKendaraanId(Antrean $record, $get): ?int
    {
        // Jika update kendaraan diaktifkan
        if ($get('update_kendaraan')) {
            // Pilihan: ubah kendaraan
            if ($get('kendaraan_option') === 'change' && $get('kendaraan_id')) {
                $kendaraan = Kendaraan::find($get('kendaraan_id'));
                return $kendaraan?->jenis_kendaraan_id;
            }
            // Pilihan: kendaraan baru
            elseif ($get('kendaraan_option') === 'new') {
                return $get('new_jenis_kendaraan_id');
            }
            // Pilihan: gunakan kendaraan yang sama
            else {
                return $record->kendaraan->jenis_kendaraan_id;
            }
        }
        // Jika tidak update kendaraan, gunakan kendaraan yang sama
        else {
            return $record->kendaraan->jenis_kendaraan_id;
        }
    }

    /**
     * Helper method to get jenis_kendaraan_id from form data (for action validation)
     */
    private static function getJenisKendaraanIdFromData(Antrean $record, array $data): ?int
    {
        // Jika update kendaraan diaktifkan
        if ($data['update_kendaraan'] ?? false) {
            // Pilihan: ubah kendaraan
            if (($data['kendaraan_option'] === 'change') && !empty($data['kendaraan_id'])) {
                $kendaraan = Kendaraan::find($data['kendaraan_id']);
                return $kendaraan?->jenis_kendaraan_id;
            }
            // Pilihan: kendaraan baru
            elseif (($data['kendaraan_option'] === 'new') && !empty($data['new_jenis_kendaraan_id'])) {
                return $data['new_jenis_kendaraan_id'];
            }
            // Pilihan: gunakan kendaraan yang sama
            else {
                return $record->kendaraan->jenis_kendaraan_id;
            }
        }
        // Jika tidak update kendaraan, gunakan kendaraan yang sama
        else {
            return $record->kendaraan->jenis_kendaraan_id;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                // Panduan Riwayat Antrean
                Tables\Actions\Action::make('panduan')
                    ->label('Panduan Riwayat')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('gray')
                    ->modalHeading('📊 Panduan Riwayat Antrean')
                    ->modalContent(new HtmlString('
                        <div class="space-y-4 text-gray-900 dark:text-gray-100">
                            <!-- Header Section -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <h3 class="font-semibold text-green-900 dark:text-green-100 mb-2 flex items-center gap-2">
                                    <i class="fas fa-archive text-green-600 dark:text-green-400"></i>
                                    Tentang Riwayat Antrean
                                </h3>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    Halaman ini menampilkan semua antrean yang sudah <strong class="text-green-800 dark:text-green-200">selesai</strong>. 
                                    Data di sini bersifat read-only dan digunakan untuk tracking history servis.
                                </p>
                            </div>

                            <!-- Grid Layout -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Informasi Tambahan -->
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Kolom Tambahan</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-play-circle text-blue-500 dark:text-blue-400 flex-shrink-0"></i>
                                            <span class="text-sm text-gray-700 dark:text-gray-300"><strong>Waktu Mulai</strong> - Kapan servis dimulai</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-flag-checkered text-green-500 dark:text-green-400 flex-shrink-0"></i>
                                            <span class="text-sm text-gray-700 dark:text-gray-300"><strong>Waktu Selesai</strong> - Kapan servis berakhir</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <i class="fas fa-clock text-purple-500 dark:text-purple-400 flex-shrink-0"></i>
                                            <span class="text-sm text-gray-700 dark:text-gray-300"><strong>Durasi</strong> - Lama pengerjaan servis</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions Section -->
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Tombol Aksi</h4>
                                    <div class="space-y-2">

                                        <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded flex-shrink-0">Detail</span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Lihat informasi lengkap antrean</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded flex-shrink-0">Cetak</span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Cetak ulang bukti antrean</span>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Filter & Pencarian -->
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800">
                                <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-3 flex items-center gap-2">
                                    <i class="fas fa-search text-purple-600 dark:text-purple-400"></i>
                                    Filter & Pencarian
                                </h4>
                                <div class="text-sm text-purple-700 dark:text-purple-300">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                            <strong class="text-purple-800 dark:text-purple-200">Filter Tanggal</strong>
                                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Cari berdasarkan rentang tanggal selesai</p>
                                        </div>
                                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                            <strong class="text-purple-800 dark:text-purple-200">Filter Mekanik</strong>
                                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Lihat antrean berdasarkan mekanik</p>
                                        </div>
                                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                            <strong class="text-purple-800 dark:text-purple-200">Search</strong>
                                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Cari nama pelanggan atau plat nomor</p>
                                        </div>
                                        <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                            <strong class="text-purple-800 dark:text-purple-200">Sort</strong>
                                            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Default urutkan dari yang terbaru selesai</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Use Cases -->
                            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800">
                                <h4 class="font-semibold text-orange-900 dark:text-orange-100 mb-3 flex items-center gap-2">
                                    <i class="fas fa-tasks text-orange-600 dark:text-orange-400"></i>
                                    Kapan Menggunakan Riwayat?
                                </h4>
                                <ul class="text-sm text-orange-700 dark:text-orange-300 space-y-2">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-chart-line text-orange-500 dark:text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span><strong class="text-orange-800 dark:text-orange-200">Melacak history servis</strong> pelanggan</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-user-cog text-orange-500 dark:text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span><strong class="text-orange-800 dark:text-orange-200">Analisis kinerja</strong> mekanik dan layanan</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-file-medical text-orange-500 dark:text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span><strong class="text-orange-800 dark:text-orange-200">Rekam medis kendaraan</strong> yang pernah diservis</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-redo text-orange-500 dark:text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span><strong class="text-orange-800 dark:text-orange-200">Pelanggan repeat</strong> yang ingin servis lagi</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-database text-orange-500 dark:text-orange-400 mt-0.5 flex-shrink-0"></i>
                                        <span><strong class="text-orange-800 dark:text-orange-200">Cadangkan data</strong> untuk laporan dan audit</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    '))
                    ->modalSubmitActionLabel('Mengerti')
                    ->modalCancelAction(false),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('nomor_antrean')
                    ->label('No. Antrean')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Pelanggan')
                    ->getStateUsing(function (Antrean $record): string {
                        if ($record->pengunjung) {
                            return $record->pengunjung->nama_pengunjung;
                        }
                        if ($record->kendaraan && $record->kendaraan->pengunjung) {
                            return $record->kendaraan->pengunjung->nama_pengunjung;
                        }
                        return 'Tidak diketahui';
                    })
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->where(function ($q) use ($search) {
                                $q->whereHas('pengunjung', fn ($sub) =>
                                    $sub->where('nama_pengunjung', 'like', "%{$search}%")
                                )
                                ->orWhereHas('kendaraan.pengunjung', fn ($sub) =>
                                    $sub->where('nama_pengunjung', 'like', "%{$search}%")
                                );
                            });
                        }
                    )
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('kendaraan.nomor_plat')
                    ->label('Plat Nomor')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('layanan.nama_layanan')
    ->label('Layanan')
    ->getStateUsing(function (Antrean $record) {
        $allLayanans = $record->layanan;
        
        // Ambil jenis layanan unik
        $jenisArray = $allLayanans->pluck('jenis_layanan')->toArray();
        $uniqueJenis = array_unique($jenisArray);
        
        // Sort: Berat -> Sedang -> Ringan
        usort($uniqueJenis, function ($a, $b) {
            $order = ['berat' => 1, 'sedang' => 2, 'ringan' => 3];
            return ($order[$a] ?? 4) <=> ($order[$b] ?? 4);
        });
        
        if (empty($uniqueJenis)) {
            return [];
        }

        // Map ke label yang friendly
        $jenisMap = [
            'ringan' => 'Ringan',
            'sedang' => 'Sedang', 
            'berat' => 'Berat'
        ];
        
        return array_map(function ($jenis) use ($jenisMap) {
            return $jenisMap[$jenis] ?? $jenis;
        }, $uniqueJenis);
    })
    ->bulleted()
    ->limitList(3)
    ->expandableLimitedList(),
                
                Tables\Columns\TextColumn::make('karyawan.nama_karyawan')
                    ->label('Mekanik')
                    ->default('Belum ditugaskan')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu' => 'warning',
                        'Dikerjakan' => 'primary', 
                        'Selesai' => 'success', 
                        default => 'gray',
                    })
                    ->sortable(),
                
                
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('waktu_selesai', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // HANYA tampilkan antrean yang statusnya "Selesai"
                $query->where('status', 'Selesai');
            })
            ->filters([
                // Filter untuk hanya menampilkan hari ini saja
                Tables\Filters\Filter::make('hanya_hari_ini')
                    ->label('Filter: 📅 Hari Ini Saja')
                    ->query(function (Builder $query) {
                        $today = Carbon::today();
                        return $query->whereDate('waktu_selesai', '>=', $today);
                    })
                    ->default(true), // Default: true, jadi tampilkan hari ini saja

                Tables\Filters\Filter::make('waktu_selesai')
                    ->form([
                        Forms\Components\DatePicker::make('selesai_dari')
                            ->label('Filter: Selesai Dari Tanggal'),
                        Forms\Components\DatePicker::make('selesai_sampai')
                            ->label('Filter: Selesai Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['selesai_dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('waktu_selesai', '>=', $date),
                            )
                            ->when(
                                $data['selesai_sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('waktu_selesai', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('karyawan_id')
                    ->label('Filter: Mekanik')
                    ->relationship('karyawan', 'nama_karyawan')
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Mekanik'),
            ])
            ->actions([

                

                Tables\Actions\Action::make('lihat_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Antrean $record) => 'Detail Antrean - ' . ($record->nomor_antrean ?? 'Tidak Ada Nomor'))
                    ->modalContent(function (Antrean $record) {
                        $pelanggan = $record->pengunjung;
                        $kendaraan = $record->kendaraan;
                        $layanans = $record->layanan;
                        $mekanik = $record->karyawan;
                        
                        if (!$pelanggan || !$kendaraan) {
                            return new HtmlString('
                                <div class="p-6 text-center">
                                    <div class="text-yellow-600 dark:text-yellow-400 text-lg mb-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300">Data antrean tidak lengkap</p>
                                </div>
                            ');
                        }

                        // Convert string dates to Carbon instances safely
                        $formatDate = function ($dateString) {
                            if (!$dateString) return null;
                            try {
                                return \Carbon\Carbon::parse($dateString);
                            } catch (\Exception $e) {
                                return null;
                            }
                        };

                        $waktuMulai = $formatDate($record->waktu_mulai);
                        $waktuSelesai = $formatDate($record->waktu_selesai);
                        $createdAt = $formatDate($record->created_at);

                        // Calculate duration safely
                        $durasiText = '';
                        if ($waktuMulai && $waktuSelesai) {
                            try {
                                // Hitung total menit, lalu konversi ke jam dan menit
                                $totalMenit = $waktuMulai->diffInMinutes($waktuSelesai);
                                $jam = floor($totalMenit / 60);
                                $menit = $totalMenit % 60;
                                
                                // Format durasi: jika < 1 jam, tampilkan hanya menit
                                if ($jam > 0) {
                                    $durasiText = $jam . 'jam ' . $menit . 'menit';
                                } else {
                                    $durasiText = $menit . 'menit';
                                }
                            } catch (\Exception $e) {
                                $durasiText = 'Tidak dapat menghitung durasi';
                            }
                        }

                        return new HtmlString('
                            <div class="space-y-6">
                                <!-- Header Info -->
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-blue-900 dark:text-blue-100">' . htmlspecialchars($record->nomor_antrean ?? 'Belum Ada Nomor') . '</h3>
                                            <p class="text-sm text-blue-700 dark:text-blue-300">Status: 
                                                <span class="font-semibold ' . match($record->status) {
                                                    'Menunggu' => 'text-yellow-600 dark:text-yellow-400',
                                                    'Dikerjakan' => 'text-blue-600 dark:text-blue-400',
                                                    'Selesai' => 'text-green-600 dark:text-green-400',
                                                    default => 'text-gray-600 dark:text-gray-400'
                                                } . '">' . $record->status . '</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-blue-700 dark:text-blue-300">Waktu Daftar</p>
                                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">' . ($createdAt ? $createdAt->format('d M Y H:i') : 'Tidak diketahui') . '</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Pelanggan -->
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="fas fa-user text-blue-500"></i>
                                        Informasi Pelanggan
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($pelanggan->nama_pengunjung) . '</p>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Nomor Telepon</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                <a href="tel:' . htmlspecialchars($pelanggan->nomor_tlp) . '" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    ' . htmlspecialchars($pelanggan->nomor_tlp) . '
                                                </a>
                                            </p>
                                        </div>
                                        <div class="md:col-span-2 space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat Lengkap</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($pelanggan->alamat) . '</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Kendaraan -->
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="fas fa-motorcycle text-green-500"></i>
                                        Informasi Kendaraan
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Plat Nomor</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-lg">' . htmlspecialchars($kendaraan->nomor_plat) . '</p>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Merk/Tipe</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($kendaraan->merk?->value ?? '-') . '</p>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Kendaraan</label>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($kendaraan->jenisKendaraan->nama_jenis ?? 'Tidak diketahui') . '</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Layanan -->
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="fas fa-tools text-orange-500"></i>
                                        Layanan yang Dikerjakan
                                    </h3>
                                    <div class="space-y-3">
                                        ' . ($layanans->groupBy('jenis_layanan')->map(function ($group, $jenis) {
                                            $colorClasses = match($jenis) {
                                                'ringan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                                'sedang' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 border-green-200 dark:border-green-800',
                                                'berat' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 border-red-200 dark:border-red-800',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 border-gray-200 dark:border-gray-800'
                                            };

                                            $iconBg = match($jenis) {
                                                'ringan' => 'bg-blue-50 dark:bg-blue-900/50',
                                                'sedang' => 'bg-green-50 dark:bg-green-900/50',
                                                'berat' => 'bg-red-50 dark:bg-red-900/50',
                                                default => 'bg-gray-50 dark:bg-gray-900/50'
                                            };

                                            $iconColor = match($jenis) {
                                                'ringan' => 'text-blue-600 dark:text-blue-400',
                                                'sedang' => 'text-green-600 dark:text-green-400',
                                                'berat' => 'text-red-600 dark:text-red-400',
                                                default => 'text-gray-600 dark:text-gray-400'
                                            };

                                            $icon = match($jenis) {
                                                'ringan' => 'fa-oil-can',
                                                'sedang' => 'fa-tools',
                                                'berat' => 'fa-cogs',
                                                default => 'fa-wrench'
                                            };

                                            $label = match($jenis) {
                                                'ringan' => 'Servis Ringan',
                                                'sedang' => 'Servis Sedang',
                                                'berat' => 'Servis Berat',
                                                default => ucfirst($jenis)
                                            };
                                            
                                            return '
                                                <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full ' . $iconBg . ' flex items-center justify-center">
                                                            <i class="fas ' . $icon . ' ' . $iconColor . ' text-lg"></i>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white">' . $label . '</p>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">' . $group->count() . ' aktivitas layanan yang tersedia</p>
                                                        </div>
                                                    </div>
                                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full border ' . $colorClasses . '">
                                                        ' . ucfirst($jenis) . '
                                                    </span>
                                                </div>
                                            ';
                                        })->implode('')) . '
                                    </div>
                                </div>

                                <!-- Informasi Mekanik -->
                                ' . ($mekanik ? '
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <i class="fas fa-user-cog text-purple-500"></i>
                                        Mekanik Penanggung Jawab
                                    </h3>
                                    <div class="flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center shadow-md">
                                            <span class="text-white font-bold text-sm">' . strtoupper(substr($mekanik->nama_karyawan, 0, 1)) . '</span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white">' . htmlspecialchars($mekanik->nama_karyawan) . '</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 capitalize">' . $mekanik->role . '</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs px-2 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 rounded-full font-medium">
                                                Bertugas
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                ' : '
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                            <i class="fas fa-exclamation text-yellow-600 dark:text-yellow-400"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300">Belum Ditugaskan</p>
                                            <p class="text-xs text-yellow-600 dark:text-yellow-400">Antrean ini belum memiliki mekanik penanggung jawab</p>
                                        </div>
                                    </div>
                                </div>
                                ') . '



                                <!-- Durasi Servis -->
                                ' . ($waktuMulai && $waktuSelesai ? '
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                <i class="fas fa-stopwatch text-green-600 dark:text-green-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-green-900 dark:text-green-300">Durasi Servis</p>
                                                <p class="text-xs text-green-700 dark:text-green-400">Waktu pengerjaan total</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-green-900 dark:text-green-300">' . $durasiText . '</p>
                                        </div>
                                    </div>
                                </div>
                                ' : '') . '
                            </div>
                        ');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Hapus Riwayat Antrean Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus riwayat antrean yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                    Tables\Actions\BulkAction::make('cetak_bulk')
                        ->label('Cetak Terpilih')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->action(function ($records) {
                            // Logic untuk mencetak multiple records
                            foreach ($records as $record) {
                                // Redirect atau logic cetak
                            }
                            
                            Notification::make()
                                ->title('Berhasil')
                                ->body($records->count() . ' antrean akan dicetak')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('Belum ada riwayat antrean')
            ->emptyStateDescription('Riwayat antrean yang sudah selesai akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayatAntreans::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Tidak bisa buat baru dari riwayat
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'Selesai')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}