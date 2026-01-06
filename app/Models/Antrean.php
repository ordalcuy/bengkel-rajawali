<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\WaitingListUpdated; 
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Antrean extends Model
{
    use HasFactory;

    protected $table = 'antrean';

    protected $fillable = [
        'nomor_antrean',
        'kendaraan_id',
        'pengunjung_id',
        'karyawan_id',
        'status',
        'waktu_mulai',
        'waktu_selesai',
    ];

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }

    public function pengunjung(): BelongsTo
    {
        return $this->belongsTo(Pengunjung::class, 'pengunjung_id');
    }

    // Relasi many-to-many untuk multiple layanan
    public function layanan(): BelongsToMany
    {
        return $this->belongsToMany(Layanan::class, 'antrean_layanan');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($antrean) {
            // Jika masih menggunakan sistem lama dengan layanan_id
            if ($antrean->layanan_id) {
                $layanan = Layanan::find($antrean->layanan_id);
                if ($layanan) {
                    $prefix = match ($layanan->jenis_layanan) {
                        'ringan' => 'A',
                        'sedang' => 'B', 
                        'berat' => 'C',
                        default => 'X'
                    };

                    $lastAntrean = self::where('nomor_antrean', 'LIKE', $prefix . '%')
                        ->whereDate('created_at', Carbon::today())
                        ->latest('id')
                        ->first();

                    $nextNumber = $lastAntrean ? ((int) substr($lastAntrean->nomor_antrean, 1)) + 1 : 1;
                    $antrean->nomor_antrean = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                }
            }
        });

        static::created(function ($antrean) {
            // Jika nomor_antrean masih null (kasus multiple layanan)
            if (!$antrean->nomor_antrean) {
                $antrean->generateNomorAntrean();
            }

            // Attach layanan_ids dari request (untuk multiple)
            if (request()->has('layanan_ids')) {
                $antrean->layanan()->attach(request()->get('layanan_ids'));
            }
        });
    }

    // TAMBAHKAN METHOD INI - YANG TERLEWAT
    public function generateNomorAntrean(): void
    {
        $jenisLayanan = $this->getJenisLayananTerberat();
        
        $prefix = match ($jenisLayanan) { 
            'berat' => 'C', 
            'sedang' => 'B', 
            default => 'A' 
        };

        $lastAntrean = self::where('nomor_antrean', 'LIKE', $prefix . '%')
            ->whereDate('created_at', Carbon::today())
            ->latest('id')
            ->first();

        $nextNumber = 1;
        if ($lastAntrean) {
            $lastNumber = (int) substr($lastAntrean->nomor_antrean, 1);
            $nextNumber = $lastNumber + 1;
        }

        $this->update([
            'nomor_antrean' => $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT)
        ]);
    }

    // Method untuk menentukan jenis layanan terberat
    public function getJenisLayananTerberat(): string
    {
        // Jika sudah ada relasi layanan, gunakan yang ada
        if ($this->relationLoaded('layanan') && $this->layanan->count() > 0) {
            if ($this->layanan->contains('jenis_layanan', 'berat')) {
                return 'berat';
            } elseif ($this->layanan->contains('jenis_layanan', 'sedang')) {
                return 'sedang';
            }
            return 'ringan';
        }

        // Fallback untuk default
        return 'ringan';
    }

    public static function broadcastActiveList()
    {
        $activeAntreans = self::with(['layanan', 'karyawan'])
            ->where('status', 'Dikerjakan')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $activeList = [
            'ringan' => [],
            'sedang' => [],
            'berat' => [],
        ];

        foreach ($activeAntreans as $antrean) {
            $jenis = $antrean->getJenisLayananTerberat();
            if (isset($activeList[$jenis])) {
                $activeList[$jenis][] = [
                    'nomor_antrean' => $antrean->nomor_antrean,
                    'mekanik' => $antrean->karyawan?->nama_karyawan ?? 'N/A',
                ];
            }
        }

        WaitingListUpdated::dispatch($activeList);
    }

    public function getNamaPelangganAttribute(): string
    {
        if ($this->pengunjung) {
            if ($this->kendaraan && $this->kendaraan->pengunjung 
                && $this->kendaraan->pengunjung->id !== $this->pengunjung->id) {
                return $this->pengunjung->nama_pengunjung .
                       " (kendaraan: " . $this->kendaraan->pengunjung->nama_pengunjung . ")";
            }
            return $this->pengunjung->nama_pengunjung;
        }

        if ($this->kendaraan && $this->kendaraan->pengunjung) {
            return $this->kendaraan->pengunjung->nama_pengunjung;
        }

        return 'Data Tidak Ditemukan';
    }

    // Accessor untuk mendapatkan jenis layanan (untuk tampilan)
    public function getJenisLayananAttribute(): string
    {
        return ucfirst($this->getJenisLayananTerberat());
    }
}