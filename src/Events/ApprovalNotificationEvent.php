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
     * @var mixed|string
     */
    public mixed $type;

    /**
     * Create a new event instance.
     */
    public function __construct($message, ApprovableModel|null $model, $type = 'SUCCESS')
    {
        $this->model = $model;
        $this->type = $type;
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
