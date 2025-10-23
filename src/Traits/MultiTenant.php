<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Illuminate\Support\Facades\Auth;
use RingleSoft\LaravelProcessApproval\Scopes\MultiTenantScope;

trait MultiTenant
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        parent::booted();
        static::creating(static function ($model) {
            $user = Auth::user();
            $tenantField = config('process_approval.multi_tenancy_field', 'tenant_id');
            if ($user && !empty($user->{$tenantField})) {
                $model->tenant_id = $user->{$tenantField};
            }
        });
        static::addGlobalScope(new MultiTenantScope());
    }
}
