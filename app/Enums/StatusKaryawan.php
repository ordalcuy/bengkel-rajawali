<?php

namespace App\Enums;

enum StatusKaryawan: string
{
    case AKTIF = 'aktif';
    case CUTI = 'cuti';
    case SAKIT = 'sakit';
    case TIDAK_AKTIF = 'tidak_aktif';

    /**
     * Get the display label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::AKTIF => 'Aktif',
            self::CUTI => 'Cuti',
            self::SAKIT => 'Sakit',
            self::TIDAK_AKTIF => 'Tidak Aktif',
        };
    }

    /**
     * Get the badge color for Filament display
     */
    public function color(): string
    {
        return match($this) {
            self::AKTIF => 'success',
            self::CUTI => 'warning',
            self::SAKIT => 'danger',
            self::TIDAK_AKTIF => 'gray',
        };
    }

    /**
     * Get the icon for the status
     */
    public function icon(): string
    {
        return match($this) {
            self::AKTIF => 'heroicon-o-check-circle',
            self::CUTI => 'heroicon-o-calendar',
            self::SAKIT => 'heroicon-o-heart',
            self::TIDAK_AKTIF => 'heroicon-o-x-circle',
        };
    }

    /**
     * Check if this status allows assignment to antrean
     */
    public function canBeAssigned(): bool
    {
        return $this === self::AKTIF;
    }

    /**
     * Get all status options for forms
     */
    public static function options(): array
    {
        return [
            self::AKTIF->value => self::AKTIF->label(),
            self::CUTI->value => self::CUTI->label(),
            self::SAKIT->value => self::SAKIT->label(),
            self::TIDAK_AKTIF->value => self::TIDAK_AKTIF->label(),
        ];
    }
}
