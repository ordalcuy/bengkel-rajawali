<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntreanDipanggil implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // PERBAIKAN: Izinkan semua properti untuk bernilai null
    public ?string $nomorPanggil;
    public ?string $namaLayanan;
    public ?string $namaMekanik;
    public ?string $platNomor;
    public ?string $nomorBerikutnya;

    public function __construct(
        ?string $nomorPanggil,
        ?string $namaLayanan,
        ?string $namaMekanik,
        ?string $platNomor,
        ?string $nomorBerikutnya
    ) {
        $this->nomorPanggil = $nomorPanggil;
        $this->namaLayanan = $namaLayanan;
        $this->namaMekanik = $namaMekanik;
        $this->platNomor = $platNomor;
        $this->nomorBerikutnya = $nomorBerikutnya;
    }

    public function broadcastOn(): array
    {
        return [new Channel('display-channel')];
    }
}