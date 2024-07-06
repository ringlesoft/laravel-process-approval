<?php

namespace RingleSoft\LaravelProcessApproval\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;

class ProcessReturnedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public ProcessApproval $approval)
    {
    }


    public static function dispatch($payload = null)
    {
        return app(Dispatcher::class)->dispatch(new static($payload));
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
