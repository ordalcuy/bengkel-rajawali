<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LayananResource\Pages;
use App\Filament\Resources\LayananResource\RelationManagers;
use App\Models\Layanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\JenisKendaraan;
use App\Models\Kendaraan;

class LayananResource extends Resource
{
        protected static ?string $model = Layanan::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Manajemen Bengkel';

    protected static ?string $label = 'Layanan';
    protected static ?string $pluralLabel = 'Layanan';

    // Hanya Owner yang bisa melihat menu ini
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('owner');
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nama_layanan')
                ->label('Nama Layanan')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->validationMessages([
                    'unique' => 'Nama layanan ini sudah ada.',
                ]),
            Forms\Components\Select::make('jenis_layanan')
                ->label('Jenis Layanan')
                ->placeholder('Pilih jenis layanan...')
                ->options([
                    'ringan' => 'Ringan',
                    'sedang' => 'Sedang',
                    'berat' => 'Berat',
                ])
                ->required(),
                
            // CheckboxList untuk memilih jenis kendaraan yang diizinkan
            // BARIS INI DIHAPUS:
            // Forms\Components\CheckboxList::make('jenis_kendaraan_akses')
            //     ->label('Dapat Diakses oleh Jenis Kendaraan')
            //     ->options(JenisKendaraan::all()->pluck('nama_jenis', 'id'))
            //     ->columns(2) 
            //     ->required(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_layanan')->searchable(),
                Tables\Columns\TextColumn::make('jenis_layanan')
                    ->label('Jenis Layanan')
                    ->placeholder('Pilih Jenis Layanan...')
                    ->getStateUsing(function ($record) {
                        return match($record->jenis_layanan) {
                            'ringan' => 'Ringan',
                            'sedang' => 'Sedang',
                            'berat' => 'Berat',
                            default => $record->jenis_layanan
                        };
                    }),
            ])
                
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Hapus Layanan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus layanan ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLayanans::route('/'),
            'create' => Pages\CreateLayanan::route('/create'),
            'edit' => Pages\EditLayanan::route('/{record}/edit'),
        ];
    }
}
