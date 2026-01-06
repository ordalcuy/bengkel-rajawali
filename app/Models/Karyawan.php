<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\StatusKaryawan;

class Karyawan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'karyawan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_karyawan',
        'alamat',
        'no_tlp',
        'role',
        'status',
    ];

    /**
     * Attribute casting
     *
     * @var array
     */
    protected $casts = [
        'status' => StatusKaryawan::class,
    ];

    /**
     * Mendefinisikan relasi "one-to-many" ke model Antrean.
     * Satu karyawan bisa menangani banyak antrean.
     */
    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class, 'karyawan_id');
    }

    /**
     * Scope untuk filter karyawan yang aktif saja
     */
    public function scopeAktif($query)
    {
        return $query->where('status', StatusKaryawan::AKTIF);
    }

    /**
     * Scope untuk filter karyawan yang bisa ditugaskan
     * (hanya karyawan dengan status Aktif)
     */
    public function scopeBisaDitugaskan($query)
    {
        return $query->where('status', StatusKaryawan::AKTIF);
    }

    /**
     * Check if karyawan is currently aktif
     */
    public function isAktif(): bool
    {
        return $this->status === StatusKaryawan::AKTIF;
    }

    /**
     * Check if karyawan can be assigned to antrean
     */
    public function bisaDitugaskan(): bool
    {
        return $this->status->canBeAssigned();
    }
}