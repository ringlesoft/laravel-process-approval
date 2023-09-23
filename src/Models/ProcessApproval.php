<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessApproval extends Model
{
    public $guarded = ['id'];

    public function user()
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
