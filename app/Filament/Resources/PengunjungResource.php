<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengunjungResource\Pages;
use App\Filament\Resources\PengunjungResource\RelationManagers;
use App\Models\Pengunjung;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Enums\MerkKendaraan;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengunjungResource extends Resource
{
    protected static ?string $model = Pengunjung::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Manajemen Bengkel';
    protected static ?int $navigationSort = 3;

    protected static ?string $label = 'Pelanggan';
    protected static ?string $pluralLabel = 'Pelanggan';

    /**
     * UBAH FUNGSI INI
     * Hanya Owner yang bisa melihat menu ini (sudah ada di prototipe Owner).
     */
    public static function canViewAny(): bool
    {
        // Hanya izinkan owner - kasir tidak perlu akses karena sudah ada di Owner panel
        return auth()->user()->hasRole('owner');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_pengunjung')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Pelanggan'), // Tambahkan label di sini
                Forms\Components\TextInput::make('nomor_tlp')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Nomor Telepon') // Tambahkan label di sini
                    ->validationMessages([
                        'unique' => 'Nomor telepon ini sudah terdaftar.',
                    ]),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nama_pengunjung')
                ->label('Nama Pelanggan') // Ubah label di sini
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query
                        ->where('nama_pengunjung', 'like', "%{$search}%")
                        ->orWhereHas('kendaraans', function ($q) use ($search) {
                            $q->where('nomor_plat', 'like', "%{$search}%");
                        });
                }),
            Tables\Columns\TextColumn::make('nomor_tlp')
                ->label('Nomor Telepon') // Ubah label di sini
                ->searchable(),

            Tables\Columns\TextColumn::make('kendaraans.nomor_plat')
                ->label('Kendaraan')
                ->formatStateUsing(function ($state, $record) {
                    $kendaraans = $record->kendaraans;

                    if ($kendaraans->isEmpty()) {
                        return '-';
                    }

                    $list = $kendaraans->map(function ($kendaraan) {
                        $merk = $kendaraan->merk?->value ?? '-';
                        return "<li>{$kendaraan->nomor_plat} ({$merk})</li>";
                    })->implode('');

                    return "<ul>{$list}</ul>";
                })
                ->html(),

            Tables\Columns\TextColumn::make('alamat')
                ->label('Alamat'),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->label('Edit')
                ->visible(fn (): bool => auth()->user()->hasRole('operator')),
            Tables\Actions\DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Hapus Pelanggan') // Ubah teks konfirmasi
                ->modalDescription('Apakah Anda yakin ingin menghapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.') // Ubah teks konfirmasi
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal')
                ->visible(fn (): bool => auth()->user()->hasRole('operator')),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Terpilih')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Hapus Pelanggan Terpilih') // Ubah teks konfirmasi
                    ->modalDescription('Apakah Anda yakin ingin menghapus pelanggan yang dipilih? Tindakan ini tidak dapat dibatalkan.') // Ubah teks konfirmasi
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->visible(fn (): bool => auth()->user()->hasRole('operator')),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            RelationManagers\KendaraansRelationManager::class,
            RelationManagers\RiwayatServisRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengunjungs::route('/'),
            'edit' => Pages\EditPengunjung::route('/{record}/edit'),
        ];
    }
}