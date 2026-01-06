<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WaitingListUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Kita akan kirimkan semua data antrean yang sedang menunggu
    public array $waitingList;

    public function __construct(array $waitingList)
    {
        $this->waitingList = $waitingList;
    }

    public function broadcastOn(): array
    {
        // Channel baru khusus untuk waiting list
        return [new Channel('waiting-list-channel')];
    }
}