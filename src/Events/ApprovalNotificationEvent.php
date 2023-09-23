<?php

namespace RingleSoft\LaravelProcessApproval\Events;

use App\Models\RequestApproval;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;

class ApprovalNotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ApprovableModel|null $model;

    /**
     * Create a new event instance.
     */
    public function __construct($message, ApprovableModel|null $model)
    {
        $this->model = $model;
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
