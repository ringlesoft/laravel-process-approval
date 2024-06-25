<?php

namespace RingleSoft\LaravelProcessApproval\Scopes;


use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MultiTenantScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        if($tenantId = Auth::user()?->{config('process_approval.multi_tenancy_field', 'tenant_id')}) {
            $builder->where(static function ($query) use ($model, $tenantId) {
                $query->where($model->getTable() . '.tenant_id', $tenantId)
                    ->orWhereNull($model->getTable() .'.tenant_id');
            });
        }

    }
}
