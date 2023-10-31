<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ProcessApprovalFlowStep extends Model
{
    protected $guarded = ['id'];
    public function role()
    {
        return $this->belongsTo(config('process_approval.roles_model'));
    }

    public function approval(): HasOne
    {
        return $this->hasOne(ProcessApproval::class)->latestOfMany();
    }


    public function processApprovalFlow()
    {
        return $this->belongsTo(ProcessApprovalFlow::class);
    }

    public function approvalForModel(ApprovableModel $model): Model|MorphMany|null
    {
        return $model->approvals()->where('process_approval_flow_step_id', $this->id)->latest()->first();
    }

}
