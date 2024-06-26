<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessApproval extends Model
{
    public $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('process_approval.users_model'));
    }
    public function getSignature()
    {
        if(method_exists($this->user, 'getSignature')) {
            return $this->user?->getSignature();
        }
        return null;
    }
}
