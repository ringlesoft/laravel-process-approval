<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ProcessApprovalStatus extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'steps' => 'array'
    ];

    public function approvable()
    {
        return $this->morphTo('approvable');
    }

    public function creator()
    {
        return $this->belongsTo(config('process_approval.users_model'), 'creator_id');
    }
}
