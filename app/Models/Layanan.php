<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    /**
     * Atribut yang dapat diisi secara massal.
     * Sesuai diagram dan penambahan field harga.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_layanan',
        'jenis_layanan',
        'jenis_kendaraan_akses', // <-- Pastikan ini ada di fillable
    ];

    /**
     * Mendefinisikan relasi "one-to-many" ke model Antrean.
     * Satu jenis layanan bisa dipilih di banyak antrean.
     */
    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class, 'layanan_id');
    }

    // app/Models/Layanan.php
protected $casts = [
    'jenis_kendaraan_akses' => 'array',
];
}