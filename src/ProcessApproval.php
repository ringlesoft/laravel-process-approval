<?php

namespace RingleSoft\LaravelProcessApproval;

use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

class ProcessApproval
{
    public function __construct()
    {
    }

    public function flows()
    {
        return ProcessApprovalFlow::query()->with('steps.role')->get();
    }

    public function steps()
    {
        return ProcessApprovalFlowStep::query()->with('role')->get();
    }

    public function createFlow(string $name, string $modelClass)
    {
        return ProcessApprovalFlow::query()->create([
            'name' => $name,
            'approvable_type' => $modelClass,
        ]);
    }

    public function deleteFlow(ProcessApprovalFlow $flow): ?bool
    {
        return $flow->delete();
    }

    public function deleteStep(ProcessApprovalFlowStep $step): ?bool
    {
        return $step->delete();
    }


}
