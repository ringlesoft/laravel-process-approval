<?php

namespace RingleSoft\LaravelProcessApproval\Resolvers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Contracts\RoleResolver;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

/**
 * Resolves roles using the step's role relation and spatie/laravel-permission's API.
 * Extend this class (or implement RoleResolver) and set `process_approval.role_resolver` to customize.
 */
class DefaultRoleResolver implements RoleResolver
{
    public function resolveRole(ProcessApprovalFlowStep $step, ApprovableModel $model): Model|null
    {
        return $step->role;
    }

    public function userHasRole(Authenticatable|null $user, ProcessApprovalFlowStep $step, ApprovableModel $model): bool
    {
        $role = $this->resolveRole($step, $model);
        return $user !== null && $role !== null && $user->hasRole($role);
    }

    public function usersWithRole(ProcessApprovalFlowStep $step, ApprovableModel $model): Collection
    {
        $role = $this->resolveRole($step, $model);
        if ($role === null) {
            return new Collection();
        }
        return (config('process_approval.users_model'))::role($role)->get();
    }
}
