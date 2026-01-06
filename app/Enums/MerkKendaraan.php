<?php

namespace App\Enums;

enum MerkKendaraan: string
{
    case HONDA = 'Honda';
    case YAMAHA = 'Yamaha';
    case SUZUKI = 'Suzuki';
    case KAWASAKI = 'Kawasaki';
    case LAINNYA = 'Lainnya';

    /**
     * Mengambil semua nilai Enum sebagai array asosiatif
     * yang cocok untuk dropdown Filament.
     */
    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->value;
        }
        return $array;
    }
}