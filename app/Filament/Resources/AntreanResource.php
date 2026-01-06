<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AntreanResource\Pages;
use App\Models\Antrean;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\JenisKendaraan;
use App\Models\Layanan;
use App\Models\Pengunjung;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Enums\MerkKendaraan;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class AntreanResource extends Resource
{
    protected static ?string $model = Antrean::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Antrean Aktif';

    protected static ?string $navigationGroup = 'Manajemen Antrean';

    protected static ?string $label = 'Antrean Aktif';

    protected static ?string $pluralLabel = 'Antrean Aktif';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('kasir');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchemaByOperation($form->getOperation()))
            ->columns(1);
    }

    private static function getFormSchemaByOperation(string $operation): array
    {
        return $operation === 'edit' 
            ? self::getEditFormSchema()
            : self::getCreationFormSchema();
    }

    /**
     * Mendefinisikan skema form untuk halaman edit.
     */
    public static function getEditFormSchema(): array
    {
        return [
            // Informasi Antrean Saat Ini
            Section::make('📋 Informasi Antrean')
                ->description('Data antrean yang sedang diedit')
                ->schema([
                    \Filament\Forms\Components\Grid::make()
                        ->schema([
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('info_nomor')
                                        ->label('Nomor Antrean')
                                        ->content(fn (?Antrean $record): string => $record?->nomor_antrean ?? '-')
                                        ->extraAttributes(['class' => 'text-xl font-bold text-primary-600 dark:text-primary-400']),
                                ])
                                ->columnSpan(1),

                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('info_pelanggan')
                                        ->label('Pelanggan')
                                        ->content(fn (?Antrean $record): string => $record?->pengunjung?->nama_pengunjung . ' (' . ($record?->pengunjung?->nomor_tlp ?? '-') . ')'),
                                ])
                                ->columnSpan(1),

                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('info_kendaraan')
                                        ->label('Kendaraan')
                                        ->content(fn (?Antrean $record): string => $record?->kendaraan?->nomor_plat . ' • ' . ($record?->kendaraan?->merk?->value ?? '-')),
                                ])
                                ->columnSpan(1),

                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('info_status')
                                        ->label('Status')
                                        ->content(function (?Antrean $record) {
                                            $status = $record?->status ?? '-';
                                            $colorClasses = match ($status) {
                                                'Menunggu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400',
                                                'Dikerjakan' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400', 
                                                'Selesai' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400', 
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400',
                                            };
                                            
                                            return new HtmlString('<span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ' . $colorClasses . '">' . $status . '</span>');
                                        }),
                                ])
                                ->columnSpan(1),
                        ])
                        ->columns(4),
                ])
                ->compact()
                ->columnSpanFull(),

            // SECTION LAYANAN SERVIS DENGAN TOGGLE - IMPROVED
            Section::make('🔧 Ubah Layanan Servis')
                ->description('Klik kartu layanan untuk memilih atau membatalkan pilihan')
                ->schema([
                    Hidden::make('jenis_layanan')
                        ->default(fn ($livewire) => $livewire->jenisLayananString)
                        ->dehydrated(),

                    // Header dengan informasi pilihan
                    Placeholder::make('selected_info')
                        ->content(function ($livewire) {
                            $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                            $selectedCount = count($values);
                            
                            if ($selectedCount === 0) {
                                return new HtmlString('
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-lg"></i>
                                            <div>
                                                <div class="font-semibold text-yellow-800 dark:text-yellow-200">Belum ada layanan dipilih</div>
                                                <div class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Klik kartu di bawah untuk memilih layanan</div>
                                            </div>
                                        </div>
                                    </div>
                                ');
                            }
                            
                            $jenisMap = [
                                'ringan' => 'Servis Ringan',
                                'sedang' => 'Servis Sedang', 
                                'berat' => 'Servis Berat'
                            ];
                            
                            $selectedNames = array_map(fn($v) => $jenisMap[$v] ?? $v, $values);
                            
                            return new HtmlString('
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-lg"></i>
                                            <div>
                                                <div class="font-semibold text-green-800 dark:text-green-200">' . $selectedCount . ' Layanan Terpilih</div>
                                                <div class="text-sm text-green-700 dark:text-green-300 mt-1">' . implode(', ', $selectedNames) . '</div>
                                            </div>
                                        </div>
                                        <span class="bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">✓ Aktif</span>
                                    </div>
                                </div>
                            ');
                        })
                        ->columnSpanFull(),

                    // Kartu jenis layanan dengan toggle - IMPROVED DESIGN
                    \Filament\Forms\Components\Grid::make()
                        ->schema([
                            // Kartu Servis Ringan
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_ringan')
                                            ->label(function ($livewire) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('ringan', $values);
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-blue-500' : '') . '">
                                                                    <i class="fas fa-oil-can text-blue-600 dark:text-blue-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Ringan</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perawatan dasar dan tune-up ringan</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-2 text-sm text-blue-600 dark:text-blue-400">
                                                                    <i class="fas fa-bolt text-xs"></i>
                                                                    
                                                                </div>
                                                                ' : '') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-full">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('ringan');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('ringan_info')
                                        ->label('')
                                        ->content(function ($livewire) {
                                            $jenisKendaraanId = $livewire->getRecord()->kendaraan->jenis_kendaraan_id;
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'ringan');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                            $isSelected = in_array('ringan', $values);
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-blue-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($livewire) => !empty($livewire->getRecord()->kendaraan->jenis_kendaraan_id)),
                                ])
                                ->extraAttributes(function ($livewire) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('ringan', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] ';
                                    $dynamicClass = $isSelected
                                        ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 shadow-lg ring-2 ring-blue-200 dark:ring-blue-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($livewire) => 
                                    $livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'ringan')
                                )
                                ->columnSpan(1),

                            // Kartu Servis Sedang
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_sedang')
                                            ->label(function ($livewire) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('sedang', $values);
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-green-500' : '') . '">
                                                                    <i class="fas fa-tools text-green-600 dark:text-green-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Sedang</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbaikan komponen utama kendaraan</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-2 text-sm text-green-600 dark:text-green-400">
                                                                    <i class="fas fa-clock text-xs"></i>
                                                                    
                                                                </div>
                                                                ' : '') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-3 py-2 rounded-full">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('sedang');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('sedang_info')
                                        ->label('')
                                        ->content(function ($livewire) {
                                            $jenisKendaraanId = $livewire->getRecord()->kendaraan->jenis_kendaraan_id;
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'sedang');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-green-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($livewire) => !empty($livewire->getRecord()->kendaraan->jenis_kendaraan_id)),
                                ])
                                ->extraAttributes(function ($livewire) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('sedang', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] ';
                                    $dynamicClass = $isSelected
                                        ? 'border-green-500 bg-green-50/50 dark:bg-green-900/20 shadow-lg ring-2 ring-green-200 dark:ring-green-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-green-300 dark:hover:border-green-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($livewire) => 
                                    $livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'sedang')
                                )
                                ->columnSpan(1),

                            // Kartu Servis Berat
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_berat')
                                            ->label(function ($livewire) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('berat', $values);
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-red-500' : '') . '">
                                                                    <i class="fas fa-cogs text-red-600 dark:text-red-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Berat</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overhaul dan perbaikan besar</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-2 text-sm text-red-600 dark:text-red-400">
                                                                    <i class="fas fa-calendar-day text-xs"></i>
                                                                    
                                                                </div>
                                                                ' : '') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-3 py-2 rounded-full">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('berat');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('berat_info')
                                        ->label('')
                                        ->content(function ($livewire) {
                                            $jenisKendaraanId = $livewire->getRecord()->kendaraan->jenis_kendaraan_id;
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'berat');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-red-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($livewire) => !empty($livewire->getRecord()->kendaraan->jenis_kendaraan_id)),
                                ])
                                ->extraAttributes(function ($livewire) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('berat', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] ';
                                    $dynamicClass = $isSelected
                                        ? 'border-red-500 bg-red-50/50 dark:bg-red-900/20 shadow-lg ring-2 ring-red-200 dark:ring-red-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-red-300 dark:hover:border-red-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($livewire) => 
                                    $livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'berat')
                                )
                                ->columnSpan(1),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),

                    // Placeholder jika tidak ada layanan sama sekali
                    Placeholder::make('no_layanan')
                        ->content(new HtmlString('
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-exclamation-triangle text-gray-400 text-xl"></i>
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak Ada Layanan Tersedia</div>
                                <div class="text-gray-500 dark:text-gray-400">Tidak ada layanan yang tersedia untuk jenis kendaraan ini</div>
                            </div>
                        '))
                        ->visible(fn($livewire) => 
                            !$livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'ringan') &&
                            !$livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'sedang') &&
                            !$livewire->hasLayananForJenis($livewire->getRecord()->kendaraan->jenis_kendaraan_id, 'berat')
                        )
                        ->columnSpanFull(),
                ])
                ->compact()
                ->columnSpanFull(),


        ];
    }

    /**
     * Mendefinisikan skema form untuk halaman create.
     */
    public static function getCreationFormSchema(): array
    {
        return [
            // SECTION: DATA PELANGGAN & KENDARAAN (2 Kolom) - LANGSUNG TANPA PENCARIAN
            \Filament\Forms\Components\Grid::make(['default' => 1, 'lg' => 2])
                ->schema([
                    // Kolom 1: Data Kendaraan
                    \Filament\Forms\Components\Card::make()
                        ->schema([


                            TextInput::make('nomor_plat')
                                ->label('Plat Nomor')
                                ->required()
                                ->maxLength(15)
                                ->placeholder('Contoh: B 1234 ABC')
                                ->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (empty($state)) {
                                        $set('kendaraan_id', null);
                                        return;
                                    }
                                    
                                    // Smart detection kendaraan
                                    $kendaraan = Kendaraan::where('nomor_plat', $state)->first();
                                    
                                    if ($kendaraan) {
                                        // Auto-fill kendaraan
                                        $set('kendaraan_id', $kendaraan->id);
                                        $set('merk', $kendaraan->merk);
                                        $set('jenis_kendaraan_id', $kendaraan->jenis_kendaraan_id);
                                        
                                        // Auto-fill pelanggan dimatikan - user harus isi manual
                                        // if ($kendaraan->pengunjung) {
                                        //     $set('pengunjung_id', $kendaraan->pengunjung_id);
                                        //     $set('nama_pengunjung', $kendaraan->pengunjung->nama_pengunjung);
                                        //     $set('nomor_tlp', $kendaraan->pengunjung->nomor_tlp);
                                        //     $set('alamat', $kendaraan->pengunjung->alamat ?? '');
                                        // }
                                    } else {
                                        // Reset jika tidak ditemukan
                                        $set('kendaraan_id', null);
                                    }
                                })
                                ->suffixIcon(fn ($get) => $get('kendaraan_id') ? 'heroicon-o-check-circle' : 'heroicon-o-magnifying-glass')
                                ->suffixIconColor(fn ($get) => $get('kendaraan_id') ? 'success' : 'gray')
                                ->helperText('Ketik plat nomor - data akan otomatis terisi jika sudah terdaftar')
                                ->columnSpanFull(),
                            
                            Select::make('merk')
                                ->label('Merk Kendaraan')
                                ->options(MerkKendaraan::toArray())
                                ->searchable()
                                ->placeholder('Pilih merk...')
                                ->helperText('Opsional - boleh dikosongkan')
                                ->disabled(fn ($get) => !empty($get('kendaraan_id')))
                                ->columnSpanFull(),
                            
                            Select::make('jenis_kendaraan_id')
                                ->label('Jenis Kendaraan')
                                ->options(JenisKendaraan::all()->pluck('nama_jenis', 'id'))
                                ->searchable()
                                ->placeholder('Pilih jenis kendaraan...')
                                ->required()
                                ->live()
                                ->disabled(fn ($get) => !empty($get('kendaraan_id')))
                                ->columnSpanFull(),
                            
                            Hidden::make('kendaraan_id'),
                        ])
                        ->columnSpan(1),

                    // Kolom 2: Data Pelanggan
                    \Filament\Forms\Components\Card::make()
                        ->schema([


                            TextInput::make('nama_pengunjung')
                                ->label('Nama Lengkap')
                                ->required()
                                ->placeholder('Masukkan nama lengkap pelanggan')
                                ->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if (empty($state)) {
                                        $set('pengunjung_id', null);
                                        return;
                                    }
                                    
                                    // Smart detection pelanggan (hanya jika kendaraan belum auto-fill)
                                    if (empty($get('kendaraan_id'))) {
                                        $pengunjung = Pengunjung::where('nama_pengunjung', 'like', "{$state}%")->first();
                                        
                                        if ($pengunjung) {
                                            // Auto-fill pelanggan
                                            $set('pengunjung_id', $pengunjung->id);
                                            $set('nomor_tlp', $pengunjung->nomor_tlp);
                                            $set('alamat', $pengunjung->alamat ?? '');
                                        } else {
                                            $set('pengunjung_id', null);
                                            // Jangan reset nomor tlp/alamat jika user sedang mengetik data baru
                                            // Tapi jika sebelumnya ada ID, mungkin perlu reset? 
                                            // Biarkan user mengisi manual jika nama baru.
                                        }
                                    }
                                })
                                ->suffixIcon(fn ($get) => $get('pengunjung_id') ? 'heroicon-o-check-circle' : 'heroicon-o-magnifying-glass')
                                ->suffixIconColor(fn ($get) => $get('pengunjung_id') ? 'success' : 'gray')
                                ->helperText('Ketik nama - data akan otomatis terisi jika sudah terdaftar')
                                ->columnSpanFull(),

                            TextInput::make('nomor_tlp')
                                ->label('Nomor Telepon')
                                ->tel()
                                ->numeric()
                                ->numeric()
                                // ->required() // Dibuat opsional
                                ->placeholder('Contoh: 081234567890')
                                ->columnSpanFull(),

                            Textarea::make('alamat')
                                ->label('Alamat Lengkap')
                                ->placeholder('Masukkan alamat lengkap untuk servis')
                                ->helperText('Opsional - boleh dikosongkan')
                                ->columnSpanFull(),
                            
                            Hidden::make('pengunjung_id'),
                        ])
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->columnSpanFull(),


            // SECTION 3: PILIH LAYANAN SERVIS
            Section::make('Layanan')
                ->visible(fn ($get) => !empty($get('jenis_kendaraan_id')))
                ->schema([

                    // Hidden field untuk jenis_layanan
                    Hidden::make('jenis_layanan')
                        ->default(fn ($livewire) => $livewire->jenisLayananString ?? '')
                        ->dehydrated(),

                    // Kartu jenis layanan dengan visibility yang benar
                    \Filament\Forms\Components\Grid::make()
                        ->schema([
                            // Kartu Servis Ringan
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_ringan')
                                            ->label(function ($livewire, $get) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('ringan', $values);
                                                $jenisKendaraanId = $get('jenis_kendaraan_id');
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'ringan');
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-blue-500 shadow-lg' : '') . ' transition-all duration-300">
                                                                    <i class="fas fa-oil-can text-blue-600 dark:text-blue-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Ringan</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perawatan dasar dan tune-up ringan</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-4 text-sm">
                                                                    <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400">
                                                                        <i class="fas fa-bolt text-xs"></i>
                                                                        
                                                                    </div>
                                                                    <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                                                        <i class="fas fa-check-circle text-xs"></i>
                                                                        <span>' . $layanans->count() . ' aktivitas layanan</span>
                                                                    </div>
                                                                </div>
                                                                ' : '
                                                                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                                                    ' . $layanans->count() . ' aktivitas layanan
                                                                </div>
                                                                ') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-3 py-2 rounded-full shadow-sm">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('ringan');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('ringan_info')
                                        ->label('')
                                        ->content(function ($get, $livewire) {
                                            $jenisKendaraanId = $get('jenis_kendaraan_id');
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'ringan');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                            $isSelected = in_array('ringan', $values);
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-blue-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($get) => !empty($get('jenis_kendaraan_id')))
                                ])
                                ->extraAttributes(function ($livewire, $get) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('ringan', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] cursor-pointer ';
                                    $dynamicClass = $isSelected
                                        ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20 shadow-lg ring-2 ring-blue-200 dark:ring-blue-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($get, $livewire) => 
                                    !empty($get('jenis_kendaraan_id')) && 
                                    $livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'ringan')
                                )
                                ->columnSpan(1),

                            // Kartu Servis Sedang
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_sedang')
                                            ->label(function ($livewire, $get) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('sedang', $values);
                                                $jenisKendaraanId = $get('jenis_kendaraan_id');
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'sedang');
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-green-500 shadow-lg' : '') . ' transition-all duration-300">
                                                                    <i class="fas fa-tools text-green-600 dark:text-green-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Sedang</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbaikan komponen utama kendaraan</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-4 text-sm">
                                                                    <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                                                        <i class="fas fa-clock text-xs"></i>
                                                                        
                                                                    </div>
                                                                    <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                                                        <i class="fas fa-check-circle text-xs"></i>
                                                                        <span>' . $layanans->count() . ' aktivitas layanan</span>
                                                                    </div>
                                                                </div>
                                                                ' : '
                                                                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                                                    ' . $layanans->count() . ' aktivitas layanan
                                                                </div>
                                                                ') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-3 py-2 rounded-full shadow-sm">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('sedang');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('sedang_info')
                                        ->label('')
                                        ->content(function ($get, $livewire) {
                                            $jenisKendaraanId = $get('jenis_kendaraan_id');
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'sedang');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                            $isSelected = in_array('sedang', $values);
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-green-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($get) => !empty($get('jenis_kendaraan_id')))
                                ])
                                ->extraAttributes(function ($livewire, $get) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('sedang', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] cursor-pointer ';
                                    $dynamicClass = $isSelected
                                        ? 'border-green-500 bg-green-50/50 dark:bg-green-900/20 shadow-lg ring-2 ring-green-200 dark:ring-green-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-green-300 dark:hover:border-green-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($get, $livewire) => 
                                    !empty($get('jenis_kendaraan_id')) && 
                                    $livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'sedang')
                                )
                                ->columnSpan(1),

                            // Kartu Servis Berat
                            \Filament\Forms\Components\Card::make()
                                ->schema([
                                    \Filament\Forms\Components\Actions::make([
                                        \Filament\Forms\Components\Actions\Action::make('toggle_berat')
                                            ->label(function ($livewire, $get) {
                                                $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                                $isSelected = in_array('berat', $values);
                                                $jenisKendaraanId = $get('jenis_kendaraan_id');
                                                $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'berat');
                                                
                                                return new HtmlString('
                                                    <div class="flex items-start justify-between w-full p-1">
                                                        <div class="flex items-start space-x-4 flex-1">
                                                            <div class="flex-shrink-0">
                                                                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center ' . ($isSelected ? 'ring-2 ring-red-500 shadow-lg' : '') . ' transition-all duration-300">
                                                                    <i class="fas fa-cogs text-red-600 dark:text-red-400 text-lg"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="text-lg font-semibold text-gray-900 dark:text-white">Servis Berat</div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overhaul dan perbaikan besar</div>
                                                                
                                                                ' . ($isSelected ? '
                                                                <div class="mt-3 flex items-center space-x-4 text-sm">
                                                                    <div class="flex items-center space-x-2 text-red-600 dark:text-red-400">
                                                                        <i class="fas fa-calendar-day text-xs"></i>
                                                                        
                                                                    </div>
                                                                    <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                                                        <i class="fas fa-check-circle text-xs"></i>
                                                                        <span>' . $layanans->count() . ' aktivitas layanan</span>
                                                                    </div>
                                                                </div>
                                                                ' : '
                                                                <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                                                    ' . $layanans->count() . ' aktivitas layanan
                                                                </div>
                                                                ') . '
                                                            </div>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            ' . ($isSelected ? '
                                                            <div class="flex items-center space-x-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-3 py-2 rounded-full shadow-sm">
                                                                <i class="fas fa-check text-sm"></i>
                                                                <span class="text-sm font-medium">Terpilih</span>
                                                            </div>
                                                            ' : '
                                                            <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-3 py-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                                                                <span class="text-sm font-medium">Pilih</span>
                                                            </div>
                                                            ') . '
                                                        </div>
                                                    </div>
                                                ');
                                            })
                                            ->action(function ($livewire) {
                                                $livewire->toggleJenisLayanan('berat');
                                            })
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'w-full !p-3 !justify-start hover:bg-transparent transition-all duration-200']),
                                    ])->columnSpanFull(),
                                    
                                    \Filament\Forms\Components\Placeholder::make('berat_info')
                                        ->label('')
                                        ->content(function ($get, $livewire) {
                                            $jenisKendaraanId = $get('jenis_kendaraan_id');
                                            if (!$jenisKendaraanId) return '';
                                            
                                            $layanans = $livewire->getLayananForJenis($jenisKendaraanId, 'berat');
                                            
                                            if ($layanans->isEmpty()) return '';
                                            
                                            $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                            $isSelected = in_array('berat', $values);
                                            
                                            $html = '<div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                                <div class="text-xs font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center space-x-2">
                                                    <i class="fas fa-list-check text-red-500"></i>
                                                    <span>Layanan yang Tersedia:</span>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2">';
                                            
                                            foreach ($layanans as $layanan) {
                                                $html .= '
                                                <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 hover:shadow-sm transition-shadow">
                                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span class="text-xs text-gray-900 dark:text-gray-100">' . htmlspecialchars($layanan->nama_layanan) . '</span>
                                                </div>';
                                            }
                                            
                                            return new HtmlString($html . '</div></div>');
                                        })
                                        ->visible(fn($get) => !empty($get('jenis_kendaraan_id')))
                                ])
                                ->extraAttributes(function ($livewire, $get) {
                                    $values = $livewire->jenisLayananString ? explode(',', $livewire->jenisLayananString) : [];
                                    $isSelected = in_array('berat', $values);
                                    
                                    $baseClass = 'transition-all duration-300 hover:scale-[1.02] cursor-pointer ';
                                    $dynamicClass = $isSelected
                                        ? 'border-red-500 bg-red-50/50 dark:bg-red-900/20 shadow-lg ring-2 ring-red-200 dark:ring-red-800'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-red-300 dark:hover:border-red-600 hover:shadow-md';
                                    return ['class' => $baseClass . $dynamicClass];
                                })
                                ->visible(fn($get, $livewire) => 
                                    !empty($get('jenis_kendaraan_id')) && 
                                    $livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'berat')
                                )
                                ->columnSpan(1),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),

                    // Placeholder jika tidak ada layanan sama sekali
                    Placeholder::make('no_layanan')
                        ->content(new HtmlString('
                            <div class="text-center py-12">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-exclamation-triangle text-gray-400 text-2xl"></i>
                                </div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white mb-2">Tidak Ada Layanan Tersedia</div>
                                <div class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                    Tidak ada layanan yang tersedia untuk jenis kendaraan ini. 
                                    Silakan hubungi administrator untuk menambahkan layanan yang sesuai.
                                </div>
                            </div>
                        '))
                        ->visible(fn($get, $livewire) => 
                            !empty($get('jenis_kendaraan_id')) && 
                            !$livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'ringan') &&
                            !$livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'sedang') &&
                            !$livewire->hasLayananForJenis($get('jenis_kendaraan_id'), 'berat')
                        )
                        ->columnSpanFull(),
                ])
                ->id('step-3-section')
                ->columnSpanFull(),


        ];
    }

    public static function getAvailableJenisLayanan(Get $get, bool $fromPlat = false): array
    {
        $jenisKendaraanId = null;
        
        if ($fromPlat) {
            $kendaraan = Kendaraan::find($get('selected_kendaraan_id'));
            $jenisKendaraanId = $kendaraan?->jenis_kendaraan_id;
        } else {
            if ($get('is_new_vehicle')) {
                $jenisKendaraanId = $get('jenis_kendaraan_id');
            } else if ($get('kendaraan_id')) {
                $kendaraan = Kendaraan::find($get('kendaraan_id'));
                $jenisKendaraanId = $kendaraan?->jenis_kendaraan_id;
            }
        }
        
        if (!$jenisKendaraanId) return [];

        $layanans = Layanan::query()
            ->whereJsonContains('jenis_kendaraan_akses', (int) $jenisKendaraanId)
            ->get();

        $grouped = [
            'ringan' => [
                'count' => $layanans->where('jenis_layanan', 'ringan')->count(),
                'layanans' => $layanans->where('jenis_layanan', 'ringan')->pluck('nama_layanan')->toArray(),
            ],
            'sedang' => [
                'count' => $layanans->where('jenis_layanan', 'sedang')->count(),
                'layanans' => $layanans->where('jenis_layanan', 'sedang')->pluck('nama_layanan')->toArray(),
            ],
            'berat' => [
                'count' => $layanans->where('jenis_layanan', 'berat')->count(),
                'layanans' => $layanans->where('jenis_layanan', 'berat')->pluck('nama_layanan')->toArray(),
            ],
        ];
        
        return array_filter($grouped, fn($group) => $group['count'] > 0);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('panduan')
                    ->label('📋 Panduan Antrean')
                    ->icon('heroicon-o-question-mark-circle')
                    ->color('gray')
                    ->modalHeading('📋 Panduan Antrean Aktif')
                    ->modalContent(new HtmlString('
                        <div class="space-y-4 text-gray-900 dark:text-gray-100">
                            <!-- Header Section -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2 flex items-center gap-2">
                                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400"></i>
                                    Tentang Antrean Aktif
                                </h3>
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Halaman ini menampilkan antrean yang sedang berjalan. Default: antrean hari ini. Gunakan filter untuk melihat antrean kemarin/menginap.
                                </p>
                            </div>

                            <!-- Sistem Tanggal Operasional -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                <h4 class="font-semibold text-green-900 dark:text-green-100 mb-3 flex items-center gap-2">
                                    <i class="fas fa-calendar-day text-green-600 dark:text-green-400"></i>
                                    Sistem Tanggal Operasional
                                </h4>
                                <div class="space-y-2 text-sm text-green-700 dark:text-green-300">
                                    <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg">
                                        <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                                        <span><strong>Default:</strong> Hanya tampilkan antrean hari ini</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full flex-shrink-0"></span>
                                        <span><strong>Filter "Tampilkan Semua Aktif":</strong> Lihat antrean dari semua tanggal yang belum selesai</span>
                                    </div>
                                    <div class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
                                        <span><strong>Badge "Menginap":</strong> Antrean dari hari sebelumnya akan ditandai khusus</span>
                                    </div>
                                </div>
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
                    ->sortable()
                    ->color('primary')
                    ->weight('bold')
                    ->description(fn (Antrean $record) => $record->created_at->format('H:i'))
                    ->icon('heroicon-o-clipboard-document-list')
                    ->iconColor('primary'),

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
                    ->searchable()
                    ->sortable()
                    ->description(fn (Antrean $record) => $record->pengunjung?->nomor_tlp ?? '-')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('kendaraan.nomor_plat')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Antrean $record) => $record->kendaraan?->merk?->value ?? '-')
                    ->icon('heroicon-o-identification'),

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
                    ->placeholder('Belum ditugaskan')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon('heroicon-o-wrench'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Menunggu' => 'warning',
                        'Dikerjakan' => 'primary',
                        'Selesai' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Menunggu' => 'heroicon-o-clock',
                        'Dikerjakan' => 'heroicon-o-cog-6-tooth',
                        'Selesai' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Daftar')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->color('gray')
                    ->description(fn (Antrean $record) => $record->created_at->locale('id')->diffForHumans()),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                // PERBAIKAN: Tampilkan SEMUA antrean aktif (termasuk kemarin) secara default
                $query->where('status', '!=', 'Selesai');
            })
            ->filters([
                // Filter untuk hanya menampilkan hari ini saja
                Tables\Filters\Filter::make('hanya_hari_ini')
                    ->label('Filter: 📅 Hari Ini Saja')
                    ->query(function (Builder $query) {
                        $today = Carbon::today();
                        return $query->whereDate('created_at', '>=', $today);
                    })
                    ->default(true), // Default: true, jadi tampilkan hari ini saja

                // Filter berdasarkan range tanggal
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Filter: Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Filter: Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                // Filter status
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Antrean')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Dikerjakan' => 'Dikerjakan',
                    ])
                    ->placeholder('Semua Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('tugaskanMekanik')
                    ->label('Tugaskan')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (Antrean $record): bool => $record->status === 'Menunggu')
                    ->modalHeading(fn (Antrean $record) => 'Tugaskan Mekanik - Antrean ' . $record->nomor_antrean)
                    ->form([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Mekanik / Helper')
                            ->placeholder('Pilih mekanik...')
                            ->options(function (Antrean $record) {
                                $busyEmployeeIds = Antrean::where('status', 'Dikerjakan')->pluck('karyawan_id')->toArray();
                                
                                $serviceLevels = ['ringan' => 1, 'sedang' => 2, 'berat' => 3];
                                $maxLevel = $record->layanan()
                                                   ->pluck('jenis_layanan')
                                                   ->map(fn ($jl) => $serviceLevels[$jl] ?? 1)
                                                   ->max();
                                $highestServiceType = array_search($maxLevel, $serviceLevels) ?: 'ringan';

                                $query = ($highestServiceType === 'ringan')
                                    ? Karyawan::query()->bisaDitugaskan()->whereIn('role', ['mekanik', 'helper'])
                                    : Karyawan::query()->bisaDitugaskan()->where('role', 'mekanik');
                                    
                                return $query->whereNotIn('id', $busyEmployeeIds)
                                    ->get()
                                    ->mapWithKeys(function ($karyawan) {
                                        $roleLabel = ucfirst($karyawan->role);
                                        return [$karyawan->id => "{$karyawan->nama_karyawan} - {$roleLabel}"];
                                    });
                            })
                            ->helperText('Hanya menampilkan karyawan dengan status Aktif yang tidak sedang mengerjakan antrean lain')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (Antrean $record, array $data): void {
                        $record->update([
                            'karyawan_id' => $data['karyawan_id'], 
                            'status' => 'Dikerjakan', 
                            'waktu_mulai' => now()
                        ]);
                        $record->refresh();
                        Antrean::broadcastActiveList();

                        Notification::make()
                            ->title('Mekanik Berhasil Ditugaskan')
                            ->body("Antrean {$record->nomor_antrean} telah ditugaskan ke {$record->karyawan->nama_karyawan}")
                            ->success()
                            ->duration(5000)
                            ->send();
                    }),
                
                Tables\Actions\Action::make('selesaikan')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Antrean $record): bool => $record->status === 'Dikerjakan')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Antrean $record) => 'Konfirmasi Selesaikan Antrean ' . $record->nomor_antrean)
                    ->modalDescription(function (Antrean $record) {
                        $pesan = self::generatePesanSelesai($record);
                        $nomorTlp = $record->pengunjung?->nomor_tlp;
                        
                        $waButton = '';
                        if ($nomorTlp) {
                            $nomorTlpInternational = self::formatPhoneToInternational($nomorTlp);
                            $pesanEncoded = urlencode($pesan);
                            $waUrl = "https://wa.me/{$nomorTlpInternational}?text={$pesanEncoded}";
                            
                            $waButton = '
                                <div x-data="{ waSent: false }">
                                    <button type="button" 
                                            x-on:click="
                                                window.open(\'' . $waUrl . '\', \'_blank\', \'noopener,noreferrer\');
                                                waSent = true;
                                                setTimeout(() => {
                                                    const submitBtn = document.querySelector(\'[wire\\\\:click*=\\\'callMountedAction\\\']\');
                                                    if (submitBtn) {
                                                        submitBtn.innerHTML = \'Selesaikan\';
                                                        submitBtn.classList.remove(\'bg-gray-600\');
                                                        submitBtn.classList.add(\'bg-primary-600\');
                                                    }
                                                }, 100);
                                            "
                                            x-bind:disabled="waSent"
                                            x-bind:class="waSent ? 
                                                \'bg-gray-400 cursor-not-allowed\' : 
                                                \'bg-green-600 hover:bg-green-700\'"
                                            class="inline-flex items-center gap-2 px-4 py-2 !text-white font-semibold rounded-lg transition-colors duration-200 shadow-md">
                                        <template x-if="!waSent">
                                            <span class="flex items-center gap-2">
                                                <i class="fab fa-whatsapp text-lg"></i>
                                                Kirim Notifikasi WA: Servis Selesai
                                            </span>
                                        </template>
                                        <template x-if="waSent">
                                            <span class="flex items-center gap-2">
                                                <i class="fas fa-check"></i>
                                                Notifikasi WA Terkirim
                                            </span>
                                        </template>
                                    </button>
                                </div>
                            ';
                        } else {
                            $waButton = '
                                <div class="text-center text-red-600 dark:text-red-400 text-sm">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Nomor telepon tidak tersedia
                                </div>
                            ';
                        }
                        
                        return new HtmlString('
                            <div class="space-y-4" x-data>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Antrean akan dipindahkan ke History Servis dan status diubah menjadi Selesai.
                                    </p>
                                </div>
                                
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Pesan Notifikasi WhatsApp</h4>
                                    <div class="text-sm text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 p-3 rounded border max-h-40 overflow-y-auto">
                                        ' . nl2br(htmlspecialchars($pesan)) . '
                                    </div>
                                </div>
                                
                                <div class="flex justify-center pt-2">
                                    ' . $waButton . '
                                </div>
                            </div>
                        ');
                    })
                    ->modalSubmitActionLabel('Selesaikan Tanpa Kirim WA')
                    ->modalCancelActionLabel('Batal')
                    ->action(function (Antrean $record, Component $livewire) {
                        $record->update([
                            'status' => 'Selesai', 
                            'waktu_selesai' => now()
                        ]);

                        Antrean::broadcastActiveList();
                        $livewire->dispatch('playTtsEvent', nomor_antrean: $record->nomor_antrean);
                    }),
                
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Hapus Antrean Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus antrean yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    // Juga update getNavigationBadge() method:
    public static function getNavigationBadge(): ?string
    {
        // PERBAIKAN: Hitung SEMUA antrean aktif (bukan hanya hari ini)
        $count = static::getModel()::whereIn('status', ['Menunggu', 'Dikerjakan'])->count();
        return $count > 0 ? (string) $count : null;
    }

    /**
     * Generate pesan otomatis untuk notifikasi servis selesai
     */
    private static function generatePesanSelesai(Antrean $record): string
    {
        $namaPelanggan = $record->pengunjung?->nama_pengunjung ?? 'Pelanggan';
        $nomorAntrean = $record->nomor_antrean;
        $platNomor = $record->kendaraan?->nomor_plat ?? '-';
        $merkKendaraan = $record->kendaraan?->merk?->value ?? '-';
        $jenisKendaraan = $record->kendaraan?->jenisKendaraan?->nama_jenis ?? '-';
        
        // Get unique service types
        $jenisLayanan = $record->layanan->pluck('jenis_layanan')
            ->unique()
            ->map(fn($j) => ucfirst($j))
            ->join(', ');

        $waktuSelesai = now()->format('d/m/Y H:i');
        
        // URL untuk tracking status
        $trackingUrl = url('/lacak-id/' . $record->id);
        
        $pesan = "Halo {$namaPelanggan}!\n\n";
        $pesan .= "Servis kendaraan Anda telah selesai:\n";
        $pesan .= "📋 No. Antrean: {$nomorAntrean}\n";
        $pesan .= "👤 Nama Pelanggan: {$namaPelanggan}\n";
        $pesan .= "🚗 No. Plat: {$platNomor}\n";
        $pesan .= "🏍️ Jenis Kendaraan: {$merkKendaraan} - {$jenisKendaraan}\n";
        $pesan .= "🔧 Jenis Layanan: {$jenisLayanan}\n";
        $pesan .= "⏰ Selesai: {$waktuSelesai}\n\n";
        $pesan .= "Silakan datang ke bengkel untuk mengambil kendaraan Anda.\n\n";
        $pesan .= "📍 Lacak status antrean di:\n";
        $pesan .= "{$trackingUrl}\n\n";
        $pesan .= "Terima kasih atas kepercayaan Anda! 🙏\n";
        $pesan .= "— Bengkel Rajawali Motor";
        
        return $pesan;
    }

    /**
     * Generate pesan untuk notifikasi penugasan mekanik
     */
    private static function generatePesanTugaskan(Antrean $record): string
    {
        $namaPelanggan = $record->pengunjung?->nama_pengunjung ?? 'Pelanggan';
        $nomorAntrean = $record->nomor_antrean;
        $platNomor = $record->kendaraan?->nomor_plat ?? '-';
        $merkKendaraan = $record->kendaraan?->merk?->value ?? '-';
        $jenisKendaraan = $record->kendaraan?->jenisKendaraan?->nama_jenis ?? '-';
        
        // Get unique service types
        $jenisLayanan = $record->layanan->pluck('jenis_layanan')
            ->unique()
            ->map(fn($j) => ucfirst($j))
            ->join(', ');
        
        $waktuMulai = now()->format('d/m/Y H:i');
        
        // URL untuk tracking status
        $trackingUrl = url('/lacak-id/' . $record->id);
        
        $pesan = "Halo {$namaPelanggan}!\n\n";
        $pesan .= "Kendaraan Anda sedang dalam proses servis:\n";
        $pesan .= "📋 No. Antrean: {$nomorAntrean}\n";
        $pesan .= "👤 Nama Pelanggan: {$namaPelanggan}\n";
        $pesan .= "🚗 No. Plat: {$platNomor}\n";
        $pesan .= "🏍️ Jenis Kendaraan: {$merkKendaraan} - {$jenisKendaraan}\n";
        $pesan .= "🔧 Jenis Layanan: {$jenisLayanan}\n";
        $pesan .= "⏰ Mulai: {$waktuMulai}\n\n";
        $pesan .= "📍 Lacak status antrean Anda di:\n";
        $pesan .= "{$trackingUrl}\n\n";
        $pesan .= "Kami akan menginformasikan kembali ketika servis selesai.\n\n";
        $pesan .= "Terima kasih! 🙏\n";
        $pesan .= "— Bengkel Rajawali Motor";
        
        return $pesan;
    }

    /**
     * Format nomor telepon Indonesia ke format internasional (628...)
     */
    private static function formatPhoneToInternational(string $phone): string
    {
        // Hapus semua karakter non-numerik
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // Jika sudah dimulai dengan 62, biarkan
        // Jika tidak, tambahkan 62 di depan (asumsi nomor lokal)
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Generate URL WhatsApp
     */
    private static function getWhatsAppUrl(Antrean $record, string $pesan): string
    {
        $nomorTlp = $record->pengunjung?->nomor_tlp;
        
        if (!$nomorTlp) {
            return '#';
        }
        
        // Format nomor ke international (628...)
        $nomorTlpInternational = self::formatPhoneToInternational($nomorTlp);
        
        // Encode pesan untuk URL
        $pesanEncoded = urlencode($pesan);
        
        return "https://wa.me/{$nomorTlpInternational}?text={$pesanEncoded}";
    }

    /**
     * Generate tombol WhatsApp
     */
    private static function getWhatsAppButton(Antrean $record, string $pesan): HtmlString
    {
        $nomorTlp = $record->pengunjung?->nomor_tlp;
        
        if (!$nomorTlp) {
            return new HtmlString('<div class="text-center text-red-600 dark:text-red-400 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Nomor telepon tidak tersedia
            </div>');
        }
        
        $waUrl = self::getWhatsAppUrl($record, $pesan);
        
        return new HtmlString('
            <a href="' . $waUrl . '" 
               target="_blank" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                <i class="fab fa-whatsapp text-lg"></i>
                Kirim Notifikasi WA: Servis Dimulai
            </a>
        ');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAntreans::route('/'),
            'create' => Pages\CreateAntrean::route('/create'),
            'edit' => Pages\EditAntrean::route('/{record}/edit'),
        ];
    }
}