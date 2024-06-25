<?php

namespace RingleSoft\LaravelProcessApproval\Contracts;

use Illuminate\Database\Eloquent\Collection;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

interface ProcessApprovalContract
{
    /**
     * Returns a list of all approval flows
     * Retrieves the flows.
     *
     */
    public function flows(): Collection|array;

    /**
     * Returns a list of all approval flow steps
     * @return Collection|array
     */
    public function steps(): Collection|array;

    /**
     * Create a new approval flow
     * @param string $name
     * @param string $modelClass
     * @return ProcessApprovalFlow
     */
    public function createFlow(string $name, string $modelClass): ProcessApprovalFlow;

    /**
     * Delete an approval flow
     * @param int $flowId
     * @return bool|null
     */
    public function deleteFlow(int $flowId): bool|null;


    /**
     * Create a new approval flow step
     * @param int $flowId
     * @param int $roleId
     * @param String|null $action
     * @param string|int|null $tenantId
     * @return ProcessApprovalFlowStep
     */
    public function createStep(int $flowId, int $roleId, String|null $action = 'APPROVE', string|int|null $tenantId = null): ProcessApprovalFlowStep;

    /**
     * Delete an approval flow step
     * @param int $stepId
     * @return bool|null
     */
    public function deleteStep(int $stepId): bool|null;
}
