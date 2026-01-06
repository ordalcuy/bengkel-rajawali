<?php

namespace App\Filament\Resources\RiwayatAntreanResource\Pages;

use App\Filament\Resources\RiwayatAntreanResource;
use App\Models\Antrean;
use App\Models\Kendaraan;
use App\Models\Layanan;
use App\Models\JenisKendaraan;
use App\Enums\MerkKendaraan;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Card;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\AntreanResource;

class AntreLagiPage extends Page
{
    protected static string $resource = RiwayatAntreanResource::class;
    protected static string $view = 'filament.resources.riwayat-antrean-resource.pages.antre-lagi';
    public Antrean $record;
    public array $data = [];
    public $successAntreanNumber;
    public $cachedLayanans = [];
    public $jenisLayananString = '';

    public function mount(): void
    {
        $recordId = request()->query('record');
        
        if (!$recordId) {
            abort(404, 'Record ID tidak ditemukan');
        }
        
        $this->record = Antrean::findOrFail($recordId);
        
        // Preload semua layanan untuk caching
        $this->cachedLayanans = Layanan::all();
        
        // Set default values untuk form
        $this->form->fill([
            'nama_pengunjung' => $this->record->pengunjung->nama_pengunjung,
            'nomor_tlp' => $this->record->pengunjung->nomor_tlp,
            'alamat' => $this->record->pengunjung->alamat,
            'kendaraan_option' => 'existing',
            // DATA KENDARAAN UNTUK EDIT EXISTING - PASTIKAN TERISI
            'nomor_plat' => $this->record->kendaraan->nomor_plat,
            'merk' => $this->record->kendaraan->merk,
            'jenis_kendaraan_id' => $this->record->kendaraan->jenis_kendaraan_id,
        ]);

        // Set jenis layanan string dari layanan yang sudah dipilih
        $jenisLayananFromRecord = $this->record->layanan->pluck('jenis_layanan')->unique()->toArray();
        $this->jenisLayananString = implode(',', $jenisLayananFromRecord);
    }

    // Method untuk toggle jenis layanan (sama seperti di AntreanResource)
    public function toggleJenisLayanan(string $jenis): void
    {
        $currentValues = $this->jenisLayananString ? explode(',', $this->jenisLayananString) : [];
        
        if (in_array($jenis, $currentValues)) {
            // Remove jika sudah ada
            $currentValues = array_filter($currentValues, fn($value) => $value !== $jenis);
        } else {
            // Add jika belum ada
            $currentValues[] = $jenis;
        }
        
        $this->jenisLayananString = implode(',', array_unique($currentValues));
        $this->dispatch('jenis-layanan-updated', jenis: $this->jenisLayananString);
    }

    // Method untuk mendapatkan layanan berdasarkan jenis kendaraan dan jenis layanan
    public function getLayananForJenis($jenisKendaraanId, $jenisLayanan)
    {
        if (!$jenisKendaraanId) return collect([]);
        
        return Layanan::query()
            ->where('jenis_layanan', $jenisLayanan)
            ->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId)
            ->get();
    }

    // Method untuk mengecek apakah ada layanan untuk jenis tertentu
    public function hasLayananForJenis($jenisKendaraanId, $jenisLayanan): bool
    {
        return $this->getLayananForJenis($jenisKendaraanId, $jenisLayanan)->isNotEmpty();
    }

    // Method untuk mendapatkan daftar kendaraan berdasarkan pelanggan
    public function getKendaraanOptions()
    {
        // SELALU gunakan pelanggan dari record asli, meskipun data diedit
        return $this->record->pengunjung->kendaraans()
            ->with('jenisKendaraan')
            ->get()
            ->mapWithKeys(function ($kendaraan) {
                $label = sprintf(
                    '%s - %s (%s)',
                    $kendaraan->nomor_plat,
                    $kendaraan->merk->value,
                    $kendaraan->jenisKendaraan->nama_jenis
                );
                return [$kendaraan->id => $label];
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // GRID 2 KOLOM: PELANGGAN & KENDARAAN
                Grid::make([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                    'xl' => 2,
                    '2xl' => 2,
                ])
                    ->schema([
                        // KOLOM KIRI - PELANGGAN
                        Section::make('Pelanggan')
                            ->description('Data pelanggan dari riwayat sebelumnya')
                            ->schema([
                                // Toggle Perbarui Data
                                Toggle::make('update_customer_info')
                                    ->label('Edit Data Pelanggan')
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Aktifkan untuk mengedit data pelanggan')
                                    ->onColor('primary')
                                    ->offColor('gray')
                                    ->columnSpanFull(),

                                // Nama - Conditional berdasarkan toggle
                                Placeholder::make('current_nama')
                                    ->content(fn () => new HtmlString('
                                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Nama:</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($this->record->pengunjung->nama_pengunjung) . '</span>
                                        </div>
                                    '))
                                    ->visible(fn ($get) => !$get('update_customer_info'))
                                    ->columnSpanFull(),

                                TextInput::make('nama_pengunjung')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->placeholder('Masukkan nama lengkap pelanggan')
                                    ->default($this->record->pengunjung->nama_pengunjung)
                                    ->visible(fn ($get) => $get('update_customer_info'))
                                    ->columnSpanFull(),

                                // Telepon - Conditional berdasarkan toggle
                                Placeholder::make('current_tlp')
                                    ->content(fn () => new HtmlString('
                                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tlp:</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($this->record->pengunjung->nomor_tlp) . '</span>
                                        </div>
                                    '))
                                    ->visible(fn ($get) => !$get('update_customer_info'))
                                    ->columnSpanFull(),

                                TextInput::make('nomor_tlp')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->numeric()
                                    ->required()
                                    ->placeholder('Contoh: 081234567890')
                                    ->default($this->record->pengunjung->nomor_tlp)
                                    ->visible(fn ($get) => $get('update_customer_info'))
                                    ->columnSpanFull(),

                                // Alamat - Conditional berdasarkan toggle
                                Placeholder::make('current_alamat')
                                    ->content(fn () => new HtmlString('
                                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="flex justify-between mb-1">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Alamat:</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($this->record->pengunjung->alamat) . '</span>
                                        </div>
                                    '))
                                    ->visible(fn ($get) => !$get('update_customer_info'))
                                    ->columnSpanFull(),

                                Textarea::make('alamat')
                                    ->label('Alamat Lengkap')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Masukkan alamat lengkap untuk servis')
                                    ->default($this->record->pengunjung->alamat)
                                    ->visible(fn ($get) => $get('update_customer_info'))
                                    ->columnSpanFull(),
                            ])
                            ->compact()
                            ->columnSpan(1),

                        // KOLOM KANAN - KENDARAAN
                        Section::make('Kendaraan')
                            ->description('Data kendaraan dari riwayat sebelumnya')
                            ->schema([
                                // Toggle Ganti Kendaraan
                                Toggle::make('update_kendaraan')
                                    ->label('Edit Kendaraan')
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Aktifkan untuk mengedit kendaraan atau memilih kendaraan lain')
                                    ->onColor('primary')
                                    ->offColor('gray')
                                    ->columnSpanFull(),

                                // Plat Nomor - Conditional berdasarkan toggle
                                Placeholder::make('current_plat')
                                    ->content(fn () => new HtmlString('
                                        <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Plat:</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($this->record->kendaraan->nomor_plat) . '</span>
                                        </div>
                                    '))
                                    ->visible(fn ($get) => !$get('update_kendaraan'))
                                    ->columnSpanFull(),

                                TextInput::make('nomor_plat')
                                    ->label('Plat Nomor')
                                    ->required()
                                    ->maxLength(15)
                                    ->placeholder('Contoh: B 1234 ABC')
                                    ->default($this->record->kendaraan->nomor_plat)
                                    ->visible(fn ($get) => $get('update_kendaraan') && $get('kendaraan_option') === 'existing')
                                    ->columnSpanFull(),

                                // Merk - Conditional berdasarkan toggle
                                Placeholder::make('current_merk')
                                    ->content(function () {
                                        $kendaraan = $this->record->kendaraan;
                                        $merk = $kendaraan->merk ? $kendaraan->merk->value : 'N/A';
                                        return new HtmlString('
                                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Merk:</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($merk) . '</span>
                                            </div>
                                        ');
                                    })
                                    ->visible(fn ($get) => !$get('update_kendaraan'))
                                    ->columnSpanFull(),

                                Select::make('merk')
                                    ->label('Merk Kendaraan')
                                    ->options(MerkKendaraan::toArray())
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih merk...')
                                    ->default($this->record->kendaraan->merk)
                                    ->visible(fn ($get) => $get('update_kendaraan') && $get('kendaraan_option') === 'existing')
                                    ->columnSpanFull(),

                                // Jenis - Conditional berdasarkan toggle
                                Placeholder::make('current_jenis')
                                    ->content(function () {
                                        $kendaraan = $this->record->kendaraan;
                                        $jenis = $kendaraan->jenisKendaraan ? $kendaraan->jenisKendaraan->nama_jenis : 'N/A';
                                        return new HtmlString('
                                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenis:</span>
                                                <span class="text-sm font-semibold text-gray-900 dark:text-white">' . htmlspecialchars($jenis) . '</span>
                                            </div>
                                        ');
                                    })
                                    ->visible(fn ($get) => !$get('update_kendaraan'))
                                    ->columnSpanFull(),

                                Select::make('jenis_kendaraan_id')
                                    ->label('Jenis Kendaraan')
                                    ->options(JenisKendaraan::all()->pluck('nama_jenis', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih jenis...')
                                    ->live()
                                    ->default($this->record->kendaraan->jenis_kendaraan_id)
                                    ->visible(fn ($get) => $get('update_kendaraan') && $get('kendaraan_option') === 'existing')
                                    ->columnSpanFull(),

                                // Opsi Kendaraan (muncul ketika toggle ON)
                                Radio::make('kendaraan_option')
                                    ->label('Pilihan Kendaraan')
                                    ->options([
                                        'existing' => 'Edit kendaraan yang sama',
                                        'change' => 'Pilih kendaraan lain',
                                        'new' => 'Tambah kendaraan baru',
                                    ])
                                    ->default('existing')
                                    ->live()
                                    ->columnSpanFull()
                                    ->visible(fn ($get) => $get('update_kendaraan') === true)
                                    ->gridDirection('column'),

                                // Dropdown Kendaraan Lain - PERBAIKAN: SELALU gunakan pelanggan asli
                                Select::make('kendaraan_id')
                                    ->label('Pilih Kendaraan Lain')
                                    ->options(fn () => $this->getKendaraanOptions())
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih kendaraan...')
                                    ->live()
                                    ->visible(fn ($get) => 
                                        $get('update_kendaraan') === true && 
                                        $get('kendaraan_option') === 'change'
                                    )
                                    ->columnSpanFull()
                                    ->helperText('Pilih kendaraan lain yang sudah terdaftar'),

                                // Form Kendaraan Baru
                                Fieldset::make('Kendaraan Baru')
                                    ->label('Data Kendaraan Baru')
                                    ->visible(fn ($get) => 
                                        $get('update_kendaraan') === true && 
                                        $get('kendaraan_option') === 'new'
                                    )
                                    ->schema([
                                        TextInput::make('new_nomor_plat')
                                            ->label('Plat Nomor')
                                            ->required()
                                            ->maxLength(15)
                                            ->placeholder('Contoh: B 1234 ABC')
                                            ->helperText('Masukkan plat nomor kendaraan baru')
                                            ->columnSpanFull(),
                                        
                                        Select::make('new_merk')
                                            ->label('Merk Kendaraan')
                                            ->options(MerkKendaraan::toArray())
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Pilih merk...')
                                            ->helperText('Pilih merk kendaraan')
                                            ->columnSpanFull(),
                                        
                                        Select::make('new_jenis_kendaraan_id')
                                            ->label('Jenis Kendaraan')
                                            ->options(JenisKendaraan::all()->pluck('nama_jenis', 'id'))
                                            ->searchable()
                                            ->required()
                                            ->placeholder('Pilih jenis...')
                                            ->live()
                                            ->helperText('Pilih jenis kendaraan')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->columnSpanFull(),
                            ])
                            ->compact()
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),

                // SECTION BAWAH - LAYANAN SERVIS
                Section::make('Layanan Servis')
                    ->description('Pilih layanan yang akan dikerjakan untuk kendaraan')
                    ->schema([
                        // Hidden field untuk jenis_layanan
                        Hidden::make('jenis_layanan')
                            ->default(fn ($livewire) => $livewire->jenisLayananString)
                            ->dehydrated(),

                        // Kartu jenis layanan dengan toggle
                        Grid::make()
                            ->schema([
                                // Kartu Servis Ringan
                                Card::make()
                                    ->schema([
                                        \Filament\Forms\Components\Actions::make([
                                            \Filament\Forms\Components\Actions\Action::make('toggle_ringan')
                                                ->label(function ($livewire) {
                                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                    $isSelected = in_array('ringan', $values);
                                                    return new HtmlString('
                                                        <div class="flex items-center justify-between w-full">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="flex items-center justify-center w-6 h-6 rounded border-2 ' . ($isSelected ? 'bg-primary-500 border-primary-500' : 'border-gray-300 dark:border-gray-600') . '">
                                                                    ' . ($isSelected ? '
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                    ' : '') . '
                                                                </div>
                                                                <div>
                                                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Ringan</div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Layanan perawatan dasar kendaraan</div>
                                                                </div>
                                                            </div>
                                                            ' . ($isSelected ? '
                                                            <span class="text-sm text-primary-600 dark:text-primary-400 font-medium">✓ Terpilih</span>
                                                            ' : '
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">Pilih</span>
                                                            ') . '
                                                        </div>
                                                    ');
                                                })
                                                ->action(function ($livewire) {
                                                    $livewire->toggleJenisLayanan('ringan');
                                                })
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full !p-6 !justify-start hover:bg-transparent']),
                                        ])->columnSpanFull(),
                                        
                                        Placeholder::make('ringan_info')
                                            ->content(function ($get, $livewire) {
                                                $jenisKendaraanId = $this->getJenisKendaraanIdFromData($get);
                                                if (!$jenisKendaraanId) return 'Pilih jenis kendaraan terlebih dahulu';
                                                
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'ringan');
                                                
                                                if ($layanans->isEmpty()) return 'Tidak ada layanan servis ringan yang tersedia';
                                                
                                                $html = '<div class="text-sm text-gray-600 dark:text-gray-400 mt-3"><div class="font-medium mb-2">Layanan termasuk:</div><ul class="list-disc list-inside space-y-1">';
                                                foreach ($layanans as $layanan) {
                                                    $html .= '<li>' . htmlspecialchars($layanan->nama_layanan) . '</li>';
                                                }
                                                return new HtmlString($html . '</ul></div>');
                                            })
                                    ])
                                    ->extraAttributes(function ($livewire, $get) {
                                        $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                        $isSelected = in_array('ringan', $values);
                                        
                                        $baseClass = 'transition-all duration-200 hover:shadow-md border-2 ';
                                        $dynamicClass = $isSelected
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300';
                                        return ['class' => $baseClass . $dynamicClass];
                                    })
                                    ->visible(fn($get, $livewire) => 
                                        !empty($this->getJenisKendaraanIdFromData($get)) && 
                                        $livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'ringan')
                                    )
                                    ->columnSpan(1),

                                // Kartu Servis Sedang
                                Card::make()
                                    ->schema([
                                        \Filament\Forms\Components\Actions::make([
                                            \Filament\Forms\Components\Actions\Action::make('toggle_sedang')
                                                ->label(function ($livewire) {
                                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                    $isSelected = in_array('sedang', $values);
                                                    return new HtmlString('
                                                        <div class="flex items-center justify-between w-full">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="flex items-center justify-center w-6 h-6 rounded border-2 ' . ($isSelected ? 'bg-primary-500 border-primary-500' : 'border-gray-300 dark:border-gray-600') . '">
                                                                    ' . ($isSelected ? '
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                    ' : '') . '
                                                                </div>
                                                                <div>
                                                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Sedang</div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Layanan perbaikan komponen utama</div>
                                                                </div>
                                                            </div>
                                                            ' . ($isSelected ? '
                                                            <span class="text-sm text-primary-600 dark:text-primary-400 font-medium">✓ Terpilih</span>
                                                            ' : '
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">Pilih</span>
                                                            ') . '
                                                        </div>
                                                    ');
                                                })
                                                ->action(function ($livewire) {
                                                    $livewire->toggleJenisLayanan('sedang');
                                                })
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full !p-6 !justify-start hover:bg-transparent']),
                                        ])->columnSpanFull(),
                                        
                                        Placeholder::make('sedang_info')
                                            ->content(function ($get, $livewire) {
                                                $jenisKendaraanId = $this->getJenisKendaraanIdFromData($get);
                                                if (!$jenisKendaraanId) return 'Pilih jenis kendaraan terlebih dahulu';
                                                
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'sedang');
                                                
                                                if ($layanans->isEmpty()) return 'Tidak ada layanan servis sedang yang tersedia';
                                                
                                                $html = '<div class="text-sm text-gray-600 dark:text-gray-400 mt-3"><div class="font-medium mb-2">Layanan termasuk:</div><ul class="list-disc list-inside space-y-1">';
                                                foreach ($layanans as $layanan) {
                                                    $html .= '<li>' . htmlspecialchars($layanan->nama_layanan) . '</li>';
                                                }
                                                return new HtmlString($html . '</ul></div>');
                                            })
                                    ])
                                    ->extraAttributes(function ($livewire, $get) {
                                        $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                        $isSelected = in_array('sedang', $values);
                                        
                                        $baseClass = 'transition-all duration-200 hover:shadow-md border-2 ';
                                        $dynamicClass = $isSelected
                                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300';
                                        return ['class' => $baseClass . $dynamicClass];
                                    })
                                    ->visible(fn($get, $livewire) => 
                                        !empty($this->getJenisKendaraanIdFromData($get)) && 
                                        $livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'sedang')
                                    )
                                    ->columnSpan(1),

                                // Kartu Servis Berat
                                Card::make()
                                    ->schema([
                                        \Filament\Forms\Components\Actions::make([
                                            \Filament\Forms\Components\Actions\Action::make('toggle_berat')
                                                ->label(function ($livewire) {
                                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                    $isSelected = in_array('berat', $values);
                                                    return new HtmlString('
                                                        <div class="flex items-center justify-between w-full">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="flex items-center justify-center w-6 h-6 rounded border-2 ' . ($isSelected ? 'bg-primary-500 border-primary-500' : 'border-gray-300 dark:border-gray-600') . '">
                                                                    ' . ($isSelected ? '
                                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                    ' : '') . '
                                                                </div>
                                                                <div>
                                                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Berat</div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Layanan overhaul dan perbaikan besar</div>
                                                                </div>
                                                            </div>
                                                            ' . ($isSelected ? '
                                                            <span class="text-sm text-primary-600 dark:text-primary-400 font-medium">✓ Terpilih</span>
                                                            ' : '
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">Pilih</span>
                                                            ') . '
                                                        </div>
                                                    ');
                                                })
                                                ->action(function ($livewire) {
                                                    $livewire->toggleJenisLayanan('berat');
                                                })
                                                ->color('gray')
                                                ->extraAttributes(['class' => 'w-full !p-6 !justify-start hover:bg-transparent']),
                                        ])->columnSpanFull(),
                                        
                                        Placeholder::make('berat_info')
                                            ->content(function ($get, $livewire) {
                                                $jenisKendaraanId = $this->getJenisKendaraanIdFromData($get);
                                                if (!$jenisKendaraanId) return 'Pilih jenis kendaraan terlebih dahulu';
                                                
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'berat');
                                                
                                                if ($layanans->isEmpty()) return 'Tidak ada layanan servis berat yang tersedia';
                                                
                                                $html = '<div class="text-sm text-gray-600 dark:text-gray-400 mt-3"><div class="font-medium mb-2">Layanan termasuk:</div><ul class="list-disc list-inside space-y-1">';
                                                foreach ($layanans as $layanan) {
                                                    $html .= '<li>' . htmlspecialchars($layanan->nama_layanan) . '</li>';
                                                }
                                                return new HtmlString($html . '</ul></div>');
                                            })
                                    ])
                                    ->extraAttributes(function ($livewire, $get) {
                                        $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                        $isSelected = in_array('berat', $values);
                                        
                                        $baseClass = 'transition-all duration-200 hover:shadow-md border-2 ';
                                        $dynamicClass = $isSelected
                                            ? 'border-red-500 bg-red-50 dark:bg-red-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300';
                                        return ['class' => $baseClass . $dynamicClass];
                                    })
                                    ->visible(fn($get, $livewire) => 
                                        !empty($this->getJenisKendaraanIdFromData($get)) && 
                                        $livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'berat')
                                    )
                                    ->columnSpan(1),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),

                        // Placeholder jika belum memilih jenis kendaraan
                        Placeholder::make('pilih_jenis_kendaraan')
                            ->content('Silakan tentukan kendaraan terlebih dahulu untuk melihat layanan yang tersedia')
                            ->visible(fn($get) => empty($this->getJenisKendaraanIdFromData($get)))
                            ->columnSpanFull(),

                        // Placeholder jika tidak ada layanan sama sekali
                        Placeholder::make('no_layanan')
                            ->content('Tidak ada layanan yang tersedia untuk jenis kendaraan ini')
                            ->visible(fn($get, $livewire) => 
                                !empty($this->getJenisKendaraanIdFromData($get)) &&
                                !$livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'ringan') &&
                                !$livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'sedang') &&
                                !$livewire->hasLayananForJenis($this->getJenisKendaraanIdFromData($get), 'berat')
                            )
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Helper method untuk mendapatkan jenis kendaraan ID dari form data
     */
    private function getJenisKendaraanIdFromData($get): ?int
    {
        // Jika update kendaraan diaktifkan
        if ($get('update_kendaraan') === true) {
            // Pilihan: ubah kendaraan
            if ($get('kendaraan_option') === 'change' && $get('kendaraan_id')) {
                $kendaraan = Kendaraan::find($get('kendaraan_id'));
                return $kendaraan?->jenis_kendaraan_id;
            }
            // Pilihan: kendaraan baru
            elseif ($get('kendaraan_option') === 'new') {
                return $get('new_jenis_kendaraan_id');
            }
            // Pilihan: edit kendaraan yang sama
            else {
                return $get('jenis_kendaraan_id') ?: $this->record->kendaraan->jenis_kendaraan_id;
            }
        }
        // Jika tidak update kendaraan, gunakan kendaraan yang sama
        else {
            return $this->record->kendaraan->jenis_kendaraan_id;
        }
    }

    /**
     * Helper method untuk mendapatkan jenis kendaraan ID dari data array (untuk action validation)
     */
    private function getJenisKendaraanIdFromArray(array $data): ?int
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
            // Pilihan: edit kendaraan yang sama
            else {
                return $data['jenis_kendaraan_id'] ?: $this->record->kendaraan->jenis_kendaraan_id;
            }
        }
        // Jika tidak update kendaraan, gunakan kendaraan yang sama
        else {
            return $this->record->kendaraan->jenis_kendaraan_id;
        }
    }

    public function createNewAntrean(): void
    {
        $data = $this->form->getState();

        // Dapatkan layanan_ids berdasarkan jenis_layanan yang dipilih
        $jenisKendaraanId = $this->getJenisKendaraanIdFromArray($data);
        if (!$jenisKendaraanId) {
            Notification::make()
                ->title('Kesalahan')
                ->body('Jenis kendaraan tidak valid. Silakan pilih kendaraan terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        // Dapatkan semua layanan berdasarkan jenis yang dipilih
        $jenisLayananRaw = $data['jenis_layanan'] ?? $this->jenisLayananString ?? '';
        $selectedJenisLayanan = $jenisLayananRaw ? explode(',', $jenisLayananRaw) : [];
        $layananIds = [];

        foreach ($selectedJenisLayanan as $jenis) {
            $layanansForJenis = $this->getLayananForJenis($jenisKendaraanId, $jenis);
            $layananIds = array_merge($layananIds, $layanansForJenis->pluck('id')->toArray());
        }

        // Validasi layanan
        if (empty($layananIds)) {
            Notification::make()
                ->title('Error')
                ->body('Harus memilih minimal satu jenis layanan.')
                ->danger()
                ->send();
            return;
        }

        // LOGIKA FIXED: Update data yang ada, bukan buat baru
        $newAntrean = DB::transaction(function () use ($data, $layananIds) {
            
            // 1. Handle update data pelanggan - UPDATE yang ada, bukan buat baru
            $pengunjungIdToLink = $this->record->pengunjung_id;

            if ($data['update_customer_info'] ?? false) {
                // UPDATE data pelanggan yang sudah ada
                $this->record->pengunjung->update([
                    'nama_pengunjung' => $data['nama_pengunjung'],
                    'nomor_tlp' => $data['nomor_tlp'],
                    'alamat' => $data['alamat'],
                ]);
            }

            // 2. Handle update kendaraan
            $kendaraanId = $this->record->kendaraan_id;
            
            if ($data['update_kendaraan'] ?? false) {
                if ($data['kendaraan_option'] === 'change' && $data['kendaraan_id']) {
                    // Gunakan kendaraan lain yang sudah ada
                    $kendaraanId = $data['kendaraan_id'];
                } elseif ($data['kendaraan_option'] === 'new') {
                    // Buat kendaraan baru, tautkan ke pelanggan yang sama
                    $newKendaraan = Kendaraan::create([
                        'pengunjung_id' => $pengunjungIdToLink,
                        'nomor_plat' => $data['new_nomor_plat'],
                        'merk' => $data['new_merk'],
                        'jenis_kendaraan_id' => $data['new_jenis_kendaraan_id'],
                    ]);
                    $kendaraanId = $newKendaraan->id;
                } else {
                    // Update kendaraan yang sama
                    $this->record->kendaraan->update([
                        'nomor_plat' => $data['nomor_plat'],
                        'merk' => $data['merk'],
                        'jenis_kendaraan_id' => $data['jenis_kendaraan_id'],
                    ]);
                }
            }

            // 3. Buat antrean baru dengan data yang sudah di-update
            $antrean = Antrean::create([
                'pengunjung_id' => $pengunjungIdToLink,
                'kendaraan_id' => $kendaraanId,
                'status' => 'Menunggu',
                'created_at' => now(),
            ]);

            // 4. Generate nomor antrean
            if (!$antrean->nomor_antrean) {
                $antrean->generateNomorAntrean();
                $antrean->save();
            }

            // 5. Attach layanan ke antrean baru
            $antrean->layanan()->attach($layananIds);

            return $antrean;
        });

        if ($newAntrean) {
            $this->successAntreanNumber = $newAntrean->nomor_antrean;

            Notification::make()
                ->title('Antrean Berhasil Dibuat')
                ->body("Nomor antrean: {$this->successAntreanNumber}")
                ->success()
                ->send();

            // Redirect ke halaman cetak
            $this->redirect(route('antrean.cetak', ['antrean' => $newAntrean->id]));
        } else {
            Notification::make()
                ->title('Gagal Membuat Antrean')
                ->body('Terjadi kesalahan saat menyimpan data.')
                ->danger()
                ->send();
        }
    }

    protected function getCachedFormActions(): array
    {
        return $this->getFormActions();
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Antrean Baru')
                ->submit('createNewAntrean')
                ->color('primary')
                ->size('lg')
                ->icon('heroicon-o-plus-circle')
                ->keyBindings(['mod+s']),

            Action::make('cancel')
                ->label('Batal')
                ->url(RiwayatAntreanResource::getUrl('index'))
                ->color('gray')
                ->size('lg')
                ->keyBindings(['escape']),
        ];
    }

    public function getTitle(): string
    {
        return 'Buat Antrean Baru dari Riwayat';
    }

    public function getHeading(): string
    {
        return 'Buat Antrean Baru dari Riwayat';
    }

    public function getSubheading(): string
    {
        return 'Buat antrean baru untuk pelanggan ' . $this->record->pengunjung->nama_pengunjung . ' dengan opsi update data';
    }
}