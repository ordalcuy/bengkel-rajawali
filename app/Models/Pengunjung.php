<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Pengunjung extends Model
{
    use HasFactory;

    protected $table = 'pengunjung';
    // Hapus plat_kendaraan & jenis_kendaraan
    protected $fillable = ['nama_pengunjung', 'nomor_tlp', 'alamat'];

    /**
     * Satu pengunjung bisa memiliki banyak kendaraan.
     */
    public function kendaraans(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
    }

    /**
     * Relasi shortcut untuk mengambil semua riwayat servis
     * milik seorang pengunjung melalui kendaraannya.
     */
    public function riwayatServis(): HasManyThrough
    {
        return $this->hasManyThrough(Antrean::class, Kendaraan::class);
    }

    /**
     * Relasi untuk mengambil semua antrean milik pengunjung.
     */
    public function antreans(): HasManyThrough
    {
        return $this->hasManyThrough(Antrean::class, Kendaraan::class);
    }
}