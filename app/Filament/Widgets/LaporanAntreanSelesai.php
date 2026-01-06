<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use App\Filament\Exports\AntreanExcelExport;
use App\Filament\Exports\AntreanPdfExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\HtmlString;

class LaporanAntreanSelesai extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public $startDate;
    public $endDate;
    public $filterMekanik;
    public $filterJenisLayanan;



    protected function getTableFilters(): array
    {
        return [
            // Filter Periode dengan Preset
            Tables\Filters\Filter::make('periode')
                ->form([
                    Radio::make('periode_preset')
                        ->label('Pilih Periode')
                        ->options([
                            'today' => 'Hari Ini',
                            '7days' => '7 Hari Terakhir',
                            '30days' => '30 Hari Terakhir',
                            'this_month' => 'Bulan Ini',
                            'custom' => 'Custom Range',
                        ])
                        ->default('today')
                        ->reactive()
                        ->inline()
                        ->columnSpanFull(),
                    
                    DatePicker::make('custom_from')
                        ->label('Dari Tanggal')
                        ->visible(fn ($get) => $get('periode_preset') === 'custom')
                        ->reactive(),
                    
                    DatePicker::make('custom_until')
                        ->label('Sampai Tanggal')
                        ->visible(fn ($get) => $get('periode_preset') === 'custom')
                        ->reactive(),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $preset = $data['periode_preset'] ?? 'today';
                    
                    switch ($preset) {
                        case 'today':
                            $this->startDate = Carbon::today();
                            $this->endDate = Carbon::today();
                            break;
                        
                        case '7days':
                            $this->startDate = Carbon::today()->subDays(6);
                            $this->endDate = Carbon::today();
                            break;
                        
                        case '30days':
                            $this->startDate = Carbon::today()->subDays(29);
                            $this->endDate = Carbon::today();
                            break;
                        
                        case 'this_month':
                            $this->startDate = Carbon::now()->startOfMonth();
                            $this->endDate = Carbon::now()->endOfMonth();
                            break;
                        
                        case 'custom':
                            $this->startDate = $data['custom_from'] ?? null;
                            $this->endDate = $data['custom_until'] ?? null;
                            break;
                    }

                    return $query
                        ->when(
                            $this->startDate,
                            fn (Builder $query, $date): Builder => $query->whereDate('waktu_selesai', '>=', $date),
                        )
                        ->when(
                            $this->endDate,
                            fn (Builder $query, $date): Builder => $query->whereDate('waktu_selesai', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): ?string {
                    $preset = $data['periode_preset'] ?? 'today';
                    
                    return match($preset) {
                        'today' => 'Periode: Hari Ini',
                        '7days' => 'Periode: 7 Hari Terakhir',
                        '30days' => 'Periode: 30 Hari Terakhir',
                        'this_month' => 'Periode: Bulan Ini',
                        'custom' => 'Periode: ' . ($data['custom_from'] ? Carbon::parse($data['custom_from'])->format('d/m/Y') : '?') . ' - ' . ($data['custom_until'] ? Carbon::parse($data['custom_until'])->format('d/m/Y') : '?'),
                        default => null,
                    };
                }),

            // Filter Jenis Layanan
            Tables\Filters\SelectFilter::make('jenis_layanan')
                ->label('Jenis Layanan')
                ->options([
                    'ringan' => 'Servis Ringan',
                    'sedang' => 'Servis Sedang',
                    'berat' => 'Servis Berat',
                ])
                ->placeholder('Semua Jenis')
                ->query(function (Builder $query, $state) {
                    $this->filterJenisLayanan = $state['value'] ?? null;
                    return $query->when(
                        $state['value'],
                        fn (Builder $query, $value): Builder => $query->whereHas('layanan', function ($q) use ($value) {
                            $q->where('jenis_layanan', $value);
                        })
                    );
                }),
        ];
    }
    
    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('info')
                ->label('Info Laporan')
                ->icon('heroicon-o-information-circle')
                ->color('gray')
                ->modalHeading('📊 Informasi Laporan')
                ->modalContent(new HtmlString('
                    <div class="space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Fitur Filter</h4>
                            <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                                <li><strong>Periode Preset:</strong> Pilih cepat hari ini, 7 hari, 30 hari, atau bulan ini</li>
                                <li><strong>Custom Range:</strong> Tentukan tanggal spesifik sesuai kebutuhan</li>
                                <li><strong>Filter Mekanik:</strong> Lihat performa per mekanik</li>
                                <li><strong>Filter Jenis Layanan:</strong> Analisis per kategori servis</li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">Export Data</h4>
                            <ul class="text-sm text-green-700 dark:text-green-300 space-y-1 list-disc list-inside">
                                <li><strong>Export Excel:</strong> Data terstruktur dengan statistik lengkap</li>
                                <li><strong>Export PDF:</strong> Laporan profesional siap cetak</li>
                                <li>Export otomatis mengikuti filter yang aktif</li>
                            </ul>
                        </div>
                    </div>
                '))
                ->modalSubmitActionLabel('Mengerti')
                ->modalCancelAction(false),

            Tables\Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    $export = new AntreanExcelExport(
                        $this->startDate, 
                        $this->endDate,
                        $this->filterMekanik,
                        $this->filterJenisLayanan
                    );
                    return Excel::download($export, 'laporan-antrean-selesai-' . now()->format('d-m-Y-H-i-s') . '.xlsx');
                }),
            
            Tables\Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document')
                ->action(function () {
                    $export = new AntreanPdfExport(
                        $this->startDate, 
                        $this->endDate,
                        $this->filterMekanik,
                        $this->filterJenisLayanan
                    );
                    return $export->download();
                })
        ];
    }

    public function getTableQuery(): Builder
    {
        return Antrean::query()
            ->where('status', 'Selesai')
            ->with([
                'pengunjung', 
                'kendaraan.pengunjung', 
                'karyawan'
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nomor_antrean')
                ->label('No. Antrean')
                ->searchable()
                ->sortable()
                ->weight('bold'),
                
            Tables\Columns\TextColumn::make('pelanggan')
                ->label('Pelanggan')
                ->state(function (Antrean $record): string {
                    if ($record->pengunjung) {
                        return $record->pengunjung->nama_pengunjung;
                    }
                    if ($record->kendaraan && $record->kendaraan->pengunjung) {
                        return $record->kendaraan->pengunjung->nama_pengunjung;
                    }
                    return 'N/A';
                })
                ->searchable(),

            Tables\Columns\TextColumn::make('layanan')
                ->label('Jenis Layanan')
                ->state(function (Antrean $record): array {
                    $jenisLayanan = \DB::table('antrean_layanan')
                        ->join('layanan', 'antrean_layanan.layanan_id', '=', 'layanan.id')
                        ->where('antrean_layanan.antrean_id', $record->id)
                        ->pluck('layanan.jenis_layanan')
                        ->unique()
                        ->map(function($jenis) {
                            return match($jenis) {
                                'ringan' => 'Ringan',
                                'sedang' => 'Sedang',
                                'berat' => 'Berat',
                                default => ucfirst($jenis)
                            };
                        })
                        ->toArray();
                    
                    return $jenisLayanan ?: ['N/A'];
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Ringan' => 'info',
                    'Sedang' => 'warning',
                    'Berat' => 'danger',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('karyawan.nama_karyawan')
                ->label('Mekanik')
                ->searchable()
                ->sortable()
                ->icon('heroicon-m-user-circle'),

            Tables\Columns\TextColumn::make('waktu_mulai')
                ->label('Mulai')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('waktu_selesai')
                ->label('Selesai')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('durasi')
                ->label('Durasi')
                ->state(function ($record) {
                    if ($record->waktu_mulai && $record->waktu_selesai) {
                        $mulai = Carbon::parse($record->waktu_mulai);
                        $selesai = Carbon::parse($record->waktu_selesai);
                        
                        $totalMinutes = $mulai->diffInMinutes($selesai);
                        $hours = floor($totalMinutes / 60);
                        $minutes = $totalMinutes % 60;
                        
                        if ($hours > 0) {
                            return $hours . 'j ' . $minutes . 'm';
                        } else {
                            return $minutes . 'm';
                        }
                    }
                    return 'N/A';
                })
                ->badge()
                ->color(function ($record) {
                    if ($record->waktu_mulai && $record->waktu_selesai) {
                        $totalMinutes = Carbon::parse($record->waktu_mulai)->diffInMinutes(Carbon::parse($record->waktu_selesai));
                        
                        // Color coding: < 60 min = green, 60-120 = yellow, > 120 = red
                        if ($totalMinutes < 60) return 'success';
                        if ($totalMinutes < 120) return 'warning';
                        return 'danger';
                    }
                    return 'gray';
                })
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->orderByRaw("TIMESTAMPDIFF(MINUTE, waktu_mulai, waktu_selesai) {$direction}");
                }),
        ];
    }


}