<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;
use RingleSoft\LaravelProcessApproval\Traits\UsesProcessApprovalUuids;

class ProcessApprovalFlow extends Model
{
    use UsesProcessApprovalUuids;

    protected $guarded = ['id'];
    protected $with = ['steps'];

    public static function getList(): \Illuminate\Contracts\Pagination\LengthAwarePaginator|LengthAwarePaginator|array
    {
        return self::query()->paginate();
    }
    public function steps(): HasMany
    {
        return $this->hasMany(ProcessApprovalFlowStep::class);
    }
}
