<?php

namespace App\Filament\Resources\PengunjungResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RiwayatServisRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatServis';

    protected static ?string $title = 'Riwayat Servis';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nomor_antrean')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_antrean'),
                Tables\Columns\TextColumn::make('kendaraan.nomor_plat')->label('Plat Nomor'),
                Tables\Columns\TextColumn::make('layanan.nama_layanan'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('waktu_selesai')->dateTime()->label('Selesai Pada'),
            ])
            // Kosongkan aksi agar tabel ini hanya untuk dibaca (read-only)
            ->actions([])
            ->bulkActions([]);
    }
}