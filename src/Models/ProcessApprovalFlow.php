<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use App\Models\RequestApprovalFlowStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessApprovalFlow extends Model
{

    protected $guarded = ['id'];

    public static function getList()
    {
        return self::query()->with(['steps'])->paginate();
    }
    public function steps()
    {
        return $this->hasMany(ProcessApprovalFlowStep::class);
    }
}
