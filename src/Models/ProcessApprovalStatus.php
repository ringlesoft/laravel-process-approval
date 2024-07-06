<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RingleSoft\LaravelProcessApproval\Traits\MultiTenant;

class ProcessApprovalStatus extends Model
{
    use MultiTenant;
    protected $guarded = ['id'];

    protected $casts = [
        'steps' => 'array'
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo('approvable');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('process_approval.users_model'), 'creator_id');
    }
}
