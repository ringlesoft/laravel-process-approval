<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator;

class ProcessApprovalFlow extends Model
{

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
