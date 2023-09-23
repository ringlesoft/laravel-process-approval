<?php

namespace RingleSoft\LaravelProcessApproval\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;

class ProcessDiscardedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ProcessApproval $approval;

    /**
     * Create a new event instance.
     */
    public function __construct(ProcessApproval $approval)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
