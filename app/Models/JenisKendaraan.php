<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisKendaraan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'jenis_kendaraan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_jenis',
        'keterangan',
    ];

    /**
     * Satu jenis kendaraan bisa dimiliki oleh banyak data kendaraan.
     */
    public function kendaraans(): HasMany
    {
        return $this->hasMany(Kendaraan::class);
    }
}