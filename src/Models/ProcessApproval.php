<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RingleSoft\LaravelProcessApproval\Traits\MultiTenant;

class ProcessApproval extends Model
{
    use MultiTenant;
    public $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('process_approval.users_model'));
    }

    public function processApprovalFlowStep(): BelongsTo
    {
        return $this->belongsTo(ProcessApprovalFlowStep::class);
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo('approvable');
    }

    public function getSignature()
    {
        if(method_exists($this->user, 'getSignature')) {
            return $this->user?->getSignature();
        }
        return null;
    }
}
