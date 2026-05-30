<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    // Properti publik ini otomatis akan terkirim sebagai data ke frontend
    public string $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }
    /**
     * Tentukan channel tempat event ini disiarkan.
     */
    public function broadcastOn(): array
    {
        // Menggunakan public channel bernama 'chat-channel'
        return [
            new Channel('chat-channel'),
        ];
    }
}