<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\MerkKendaraan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';
    protected $fillable = ['pengunjung_id', 'nomor_plat', 'merk', 'jenis_kendaraan_id'];

    // Tambahkan properti default
    protected $attributes = [
        'merk' => 'Lainnya', // Default value
    ];

    protected $casts = [
        'merk' => MerkKendaraan::class,
    ];

    public function pengunjung(): BelongsTo
    {
        return $this->belongsTo(Pengunjung::class);
    }

    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class);
    }

    public function jenisKendaraan(): BelongsTo
    {
        return $this->belongsTo(JenisKendaraan::class, 'jenis_kendaraan_id');
    }
}