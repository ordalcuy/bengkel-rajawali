<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
use App\Enums\StatusKaryawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Bengkel'; // Grup menu

    protected static ?string $label = 'Karyawan';
    protected static ?string $pluralLabel = 'Karyawan';

    // Hanya Owner yang bisa melihat menu ini
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('owner');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_karyawan')
                    ->label('Nama Karyawan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_tlp')
                    ->label('Nomor Telepon')
                    ->tel()
                    ->required(),
                // Sesuai kolom 'role' di diagram Anda
                Forms\Components\Select::make('role')
                    ->label('Peran')
                    ->placeholder('Pilih peran...')
                    ->options([
                        'mekanik' => 'Mekanik',
                        'helper' => 'Helper',
                    ])
                    ->required(),
                
                // Status Karyawan - NEW FIELD
                Forms\Components\Select::make('status')
                    ->label('Status Karyawan')
                    ->options(StatusKaryawan::options())
                    ->default(StatusKaryawan::AKTIF->value)
                    ->required()
                    ->helperText('Hanya karyawan dengan status "Aktif" yang bisa ditugaskan ke antrean')
                    ->live(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_karyawan')
                    ->searchable()
                    ->label('Nama Karyawan')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('no_tlp')
                    ->label('Nomor Telepon'),
                
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'kasir' => 'Kasir',
                            'mekanik' => 'Mekanik',
                            'helper' => 'Helper',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color(function ($state) {
                        return match($state) {
                            'kasir' => 'success',
                            'mekanik' => 'primary',
                            'helper' => 'warning',
                            default => 'gray',
                        };
                    }),
                
                // Status Column - NEW
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (StatusKaryawan $state) => $state->label())
                    ->color(fn (StatusKaryawan $state) => $state->color())
                    ->icon(fn (StatusKaryawan $state) => $state->icon())
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter berdasarkan status
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(StatusKaryawan::options())
                    ->placeholder('Semua Status'),
                
                // Filter berdasarkan role
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'mekanik' => 'Mekanik',
                        'helper' => 'Helper',
                    ])
                    ->placeholder('Semua Peran'),
            ])
            ->defaultSort('status', 'asc') // Aktif di atas
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Hapus Karyawan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus karyawan ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal'), // Owner bisa menghapus
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Hapus Karyawan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus karyawan yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ]),
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}