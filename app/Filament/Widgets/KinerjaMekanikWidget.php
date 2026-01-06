<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Antrean;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Filament\Exports\KinerjaMekanikPdfExport;
use App\Filament\Exports\KinerjaMekanikExcelExport;
use Maatwebsite\Excel\Facades\Excel;

class KinerjaMekanikWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    
    // Properties untuk filter (sync dengan LaporanAntreanSelesai)
    public $startDate;
    public $endDate;

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return Excel::download(
                        new KinerjaMekanikExcelExport(
                            $this->startDate ?? Carbon::today(), 
                            $this->endDate ?? Carbon::today()
                        ),
                        'laporan-kinerja-mekanik-' . now()->format('d-m-Y-H-i-s') . '.xlsx'
                    );
                }),
            Tables\Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document')
                ->action(function () {
                    $export = new KinerjaMekanikPdfExport(
                        $this->startDate ?? Carbon::today(), 
                        $this->endDate ?? Carbon::today()
                    );
                    return $export->download();
                }),
        ];
    }

    public function getTableQuery(): Builder
    {
        // Query untuk mendapatkan data kinerja mekanik & helper
        return Karyawan::query()
            ->whereIn('role', ['mekanik', 'helper'])
            ->where('status', 'aktif');
    }

    protected function getTableColumns(): array
    {
        $startDate = $this->startDate ?? Carbon::today();
        $endDate = $this->endDate ?? Carbon::today();

        return [
            Tables\Columns\TextColumn::make('nama_karyawan')
                ->label('Mekanik')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->icon('heroicon-m-user-circle'),

            Tables\Columns\TextColumn::make('total_servis')
                ->label('Total Servis')
                ->state(function (Karyawan $record) use ($startDate, $endDate): int {
                    return Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->count();
                })
                ->badge()
                ->color('primary')
                ->alignCenter()
                ->sortable(query: function (Builder $query, string $direction): Builder {
                    return $query->withCount(['antreans' => function ($q) {
                        $q->where('status', 'Selesai');
                    }])->orderBy('antreans_count', $direction);
                }),

            Tables\Columns\TextColumn::make('total_durasi')
                ->label('Total Durasi')
                ->state(function (Karyawan $record) use ($startDate, $endDate): string {
                    $antreans = Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->whereNotNull('waktu_mulai')
                        ->whereNotNull('waktu_selesai')
                        ->get();
                    
                    $totalMinutes = 0;
                    foreach ($antreans as $antrean) {
                        $mulai = Carbon::parse($antrean->waktu_mulai);
                        $selesai = Carbon::parse($antrean->waktu_selesai);
                        $totalMinutes += $mulai->diffInMinutes($selesai);
                    }
                    
                    if ($totalMinutes === 0) return '-';
                    
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    
                    if ($hours > 0) {
                        return $hours . 'j ' . $minutes . 'm';
                    }
                    return $minutes . 'm';
                })
                ->alignCenter(),

            Tables\Columns\TextColumn::make('rata_durasi')
                ->label('Rata-rata Durasi')
                ->state(function (Karyawan $record) use ($startDate, $endDate): string {
                    $antreans = Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->whereNotNull('waktu_mulai')
                        ->whereNotNull('waktu_selesai')
                        ->get();
                    
                    if ($antreans->isEmpty()) return '-';
                    
                    $totalMinutes = 0;
                    foreach ($antreans as $antrean) {
                        $mulai = Carbon::parse($antrean->waktu_mulai);
                        $selesai = Carbon::parse($antrean->waktu_selesai);
                        $totalMinutes += $mulai->diffInMinutes($selesai);
                    }
                    
                    $avgMinutes = round($totalMinutes / $antreans->count());
                    
                    $hours = floor($avgMinutes / 60);
                    $minutes = $avgMinutes % 60;
                    
                    if ($hours > 0) {
                        return $hours . 'j ' . $minutes . 'm';
                    }
                    return $minutes . 'm';
                })
                ->badge()
                ->color('info')
                ->alignCenter(),

            Tables\Columns\TextColumn::make('servis_ringan')
                ->label('Ringan')
                ->state(function (Karyawan $record) use ($startDate, $endDate): int {
                    return Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->whereHas('layanan', function ($q) {
                            $q->where('jenis_layanan', 'ringan');
                        })
                        ->count();
                })
                ->badge()
                ->color('success')
                ->alignCenter(),

            Tables\Columns\TextColumn::make('servis_sedang')
                ->label('Sedang')
                ->state(function (Karyawan $record) use ($startDate, $endDate): int {
                    return Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->whereHas('layanan', function ($q) {
                            $q->where('jenis_layanan', 'sedang');
                        })
                        ->count();
                })
                ->badge()
                ->color('warning')
                ->alignCenter(),

            Tables\Columns\TextColumn::make('servis_berat')
                ->label('Berat')
                ->state(function (Karyawan $record) use ($startDate, $endDate): int {
                    return Antrean::where('karyawan_id', $record->id)
                        ->where('status', 'Selesai')
                        ->whereDate('waktu_selesai', '>=', $startDate)
                        ->whereDate('waktu_selesai', '<=', $endDate)
                        ->whereHas('layanan', function ($q) {
                            $q->where('jenis_layanan', 'berat');
                        })
                        ->count();
                })
                ->badge()
                ->color('danger')
                ->alignCenter(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('periode')
                ->form([
                    \Filament\Forms\Components\Radio::make('periode_preset')
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
                    
                    \Filament\Forms\Components\DatePicker::make('custom_from')
                        ->label('Dari Tanggal')
                        ->visible(fn ($get) => $get('periode_preset') === 'custom')
                        ->reactive(),
                    
                    \Filament\Forms\Components\DatePicker::make('custom_until')
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
                            $this->startDate = $data['custom_from'] ? Carbon::parse($data['custom_from']) : Carbon::today();
                            $this->endDate = $data['custom_until'] ? Carbon::parse($data['custom_until']) : Carbon::today();
                            break;
                    }
                    
                    return $query;
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
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada data mekanik';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Belum ada mekanik aktif yang terdaftar di sistem.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }
}
