<?php

namespace RingleSoft\LaravelProcessApproval\Scopes;


use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class MultiTenantScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        $tenantField = config('process_approval.multi_tenancy_field', 'tenant_id');
        if ($user && !empty($user->{$tenantField}) && $tenantId = $user->{$tenantField}) {
            $tableName = $model->getTable();
            $builder->where(static function ($query) use ($tableName, $tenantField, $tenantId) {
                $query->where("$tableName.$tenantField", $tenantId)
                    ->orWhereNull("$tableName.$tenantField");
            });
        }
    }
}
