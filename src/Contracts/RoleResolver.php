<?php

namespace RingleSoft\LaravelProcessApproval\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

interface RoleResolver
{
    /**
     * Resolve the role that applies to the given step for the given model
     * @param ProcessApprovalFlowStep $step
     * @param ApprovableModel $model
     * @return Model|null
     */
    public function resolveRole(ProcessApprovalFlowStep $step, ApprovableModel $model): Model|null;

    /**
     * Check if the user holds the role applicable to the given step
     * @param Authenticatable|null $user
     * @param ProcessApprovalFlowStep $step
     * @param ApprovableModel $model
     * @return bool
     */
    public function userHasRole(Authenticatable|null $user, ProcessApprovalFlowStep $step, ApprovableModel $model): bool;

    /**
     * Get the users holding the role applicable to the given step
     * @param ProcessApprovalFlowStep $step
     * @param ApprovableModel $model
     * @return Collection
     */
    public function usersWithRole(ProcessApprovalFlowStep $step, ApprovableModel $model): Collection;
}
