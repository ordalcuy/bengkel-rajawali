<?php

namespace App\Filament\Resources\AntreanResource\Pages;

use App\Filament\Resources\AntreanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ListAntreans extends ListRecords
{
    protected static string $resource = AntreanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Antrean Baru')
                ->icon('heroicon-o-plus-circle')
                ->keyBindings(['mod+n', 'alt+n'])
                ->color('primary')
                ->size('lg'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AntreanResource\Widgets\DateTimeWidget::class,
            AntreanResource\Widgets\AntreanStats::class,
        ];
    }

    protected function getListeners(): array
    {
        return [
            'refreshAntreanList' => '$refresh',
        ];
    }

    protected function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pengunjung', 'kendaraan.pengunjung', 'layanan', 'karyawan'])
            ->latest();
    }

    public function updatedTableFilters(): void
    {
        // Berikan feedback ketika filter diubah
        $this->dispatch('filters-updated');
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak Ada Antrean Aktif';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Klik tombol "Buat Antrean Baru" untuk menambahkan antrean pertama.';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('create')
                ->label('Buat Antrean Baru')
                ->url(static::getResource()::getUrl('create'))
                ->icon('heroicon-o-plus')
                ->button(),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableFiltersFormHeading(): ?string
    {
        return 'Filter';
    }
}