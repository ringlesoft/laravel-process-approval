<?php

namespace RingleSoft\LaravelProcessApproval\Models;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessApprovalFlow extends Model
{
    protected $guarded = ['id'];

    public static function getList(): LengthAwarePaginator
    {
        return self::query()->with(['steps'])->paginate();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessApprovalFlowStep::class);
    }
}
