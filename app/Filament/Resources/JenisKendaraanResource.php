<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisKendaraanResource\Pages;
use App\Models\JenisKendaraan;
use App\Models\Layanan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JenisKendaraanResource extends Resource
{
    protected static ?string $model = JenisKendaraan::class;

    protected static ?string $navigationIcon = 'fas-motorcycle';
    protected static ?string $navigationGroup = 'Manajemen Bengkel';
    protected static ?string $navigationLabel = 'Jenis Kendaraan';
    protected static ?string $modelLabel = 'Jenis Kendaraan';
    protected static ?string $pluralModelLabel = 'Jenis Kendaraan';
    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('owner');
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('nama_jenis')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('keterangan')
                ->maxLength(65535)
                ->columnSpanFull(),

            Forms\Components\Hidden::make('allowed_jenis_layanans_to_save')
                ->default('[]')
                ->dehydrated(true), 
            
            Forms\Components\CheckboxList::make('allowed_jenis_layanans')
                ->label('Dapat Mengakses Jenis Layanan')
                ->options([
                    'ringan' => 'Servis Ringan',
                    'sedang' => 'Servis Sedang',
                    'berat' => 'Servis Berat',
                ])
                ->columns(3)
                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Model $record, Forms\Set $set) {
                    if (!$record) {
                        // Untuk create baru, set empty array
                        $set('allowed_jenis_layanans', []);
                        $set('allowed_jenis_layanans_to_save', '[]');
                        return;
                    }
                    
                    // Untuk edit, baca data dari database
                    $layananAkses = Layanan::all()
                        ->filter(function ($layanan) use ($record) {
                            $akses = $layanan->jenis_kendaraan_akses;
                            
                            // Pastikan $akses adalah array
                            if (is_string($akses)) {
                                $akses = json_decode($akses, true) ?? [];
                            }
                            $akses = is_array($akses) ? $akses : [];
                            $akses = array_map('intval', $akses);
                            
                            return in_array((int) $record->id, $akses);
                        })
                        ->pluck('jenis_layanan')
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    $component->state($layananAkses);
                    $set('allowed_jenis_layanans_to_save', json_encode($layananAkses));
                })
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, $state) {
                    $set('allowed_jenis_layanans_to_save', json_encode($state ?? []));
                })
                ->dehydrated(false) 
                ->columnSpanFull(),
        ]);
}

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nama_jenis')
                ->label('Nama Jenis Kendaraan')
                ->searchable()
                ->sortable(),
            
            Tables\Columns\TextColumn::make('accessible_layanan_count')
                ->label('Layanan yang Dapat Diakses')
                ->getStateUsing(function (JenisKendaraan $record): string {
                    $currentJenisKendaraanId = (int) $record->id;
                    
                    $jenisLayanans = Layanan::whereJsonContains('jenis_kendaraan_akses', $currentJenisKendaraanId)
                        ->pluck('jenis_layanan')
                        ->unique()
                        ->map(fn($jenis) => match($jenis) {
                            'ringan' => 'Ringan',
                            'sedang' => 'Sedang',
                            'berat' => 'Berat',
                            default => $jenis
                        })
                        ->implode(', ');
                        
                    return $jenisLayanans ?: 'Tidak ada';
                }),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->label('Edit'),
            Tables\Actions\DeleteAction::make()
                ->label('Hapus')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Hapus Jenis Kendaraan')
                ->modalDescription('Apakah Anda yakin ingin menghapus jenis kendaraan ini? Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus')
                ->modalCancelActionLabel('Batal')
                ->before(function (JenisKendaraan $record) {
                    // Hapus akses dari semua layanan sebelum delete
                    DB::transaction(function () use ($record) {
                        $jenisKendaraanId = (int) $record->id;
                        $layanans = Layanan::all();
                        
                        foreach ($layanans as $layanan) {
                            $akses = $layanan->jenis_kendaraan_akses ?? [];
                            $akses = array_filter($akses, fn($id) => $id !== $jenisKendaraanId);
                            $layanan->jenis_kendaraan_akses = array_values($akses);
                            $layanan->save();
                        }
                    });
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Terpilih')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Hapus Jenis Kendaraan Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus jenis kendaraan yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'),
            ]),
        ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJenisKendaraans::route('/'),
            'create' => Pages\CreateJenisKendaraan::route('/create'),
            'edit' => Pages\EditJenisKendaraan::route('/{record}/edit'),
        ];
    }
}