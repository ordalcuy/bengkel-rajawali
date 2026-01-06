<?php

namespace App\Filament\Resources\PengunjungResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\JenisKendaraan;
use App\Enums\MerkKendaraan;

class KendaraansRelationManager extends RelationManager
{
    protected static string $relationship = 'kendaraans';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_plat')
                    ->required()->maxLength(255),
                Forms\Components\Select::make('merk')
                    ->label('Merk Kendaraan')
                    ->placeholder('Pilih merk...')
                    ->options(MerkKendaraan::toArray())
                    ->searchable()
                    ->required(),
                
                // PERBAIKAN FINAL: Gunakan ->options() yang sudah terbukti berhasil
                Forms\Components\Select::make('jenis_kendaraan_id')
                    ->label('Jenis Kendaraan')
                    ->placeholder('Pilih jenis...')
                    ->options(JenisKendaraan::all()->pluck('nama_jenis', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_plat')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_plat'),
                Tables\Columns\TextColumn::make('merk'),
                Tables\Columns\TextColumn::make('jenisKendaraan.nama_jenis')->label('Jenis'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Kendaraan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Hapus Kendaraan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus kendaraan ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]);
    }
}