<?php

namespace RingleSoft\LaravelProcessApproval\Contracts;

use Illuminate\Database\Eloquent\Collection;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

interface ProcessApprovalContract
{
    /**
     * Retrieves the flows.
     *
     */
    public function flows(): \Illuminate\Database\Eloquent\Collection|array;

    /**
     * @return Collection|array
     */
    public function steps(): \Illuminate\Database\Eloquent\Collection|array;

    /**
     * @param string $name
     * @param string $modelClass
     * @return ProcessApprovalFlow
     */
    public function createFlow(string $name, string $modelClass): ProcessApprovalFlow;

    /**
     * @param ProcessApprovalFlow|int $flow
     * @return bool|null
     */
    public function deleteFlow(int $flowId): bool|null;


    /**
     * @param int $flow
     * @param int $roleId
     * @param String|null $action
     * @return ProcessApprovalFlow
     */
    public function createStep(int $flowId, int $roleId, String|null $action = 'APPROVE'): ProcessApprovalFlowStep;

    /**
     * @param ProcessApprovalFlowStep|int $step
     * @return bool|null
     */
    public function deleteStep(int $stepId): bool|null;
}
