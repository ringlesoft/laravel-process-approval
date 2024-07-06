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
            if(Auth::user()?->{config('process_approval.multi_tenancy_field', 'tenant_id')}) {
                $model->tenant_id = Auth::user()?->{config('process_approval.multi_tenancy_field', 'tenant_id')};
            }
        });
        static::addGlobalScope(new MultiTenantScope());
    }
}
