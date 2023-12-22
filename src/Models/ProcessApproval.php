<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Config;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

/**
 * @property int $id
 * @property string $approvable_type
 * @property int $approvable_id
 * @property ?int $process_approval_flow_step_id
 * @property string $approval_action
 * @property ?string $approver_name
 * @property ?string $comment
 * @property int $user_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 *
 * @property ProcessApprovalFlowStep|null $process_approval_flow_step
 * @property ?ApprovableModel $approvable
 * @property Model $user
 */
class ProcessApproval extends Model
{
    public $guarded = ['id'];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Config::get('process_approval.users_model'));
    }

    public function getSignature()
    {
        if(method_exists($this->user, 'getSignature')) {
            return $this->user?->getSignature();
        }

        return null;
    }
}
