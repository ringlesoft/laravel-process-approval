<?php

namespace RingleSoft\LaravelProcessApproval;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RingleSoft\LaravelProcessApproval\Contracts\ProcessApprovalContract;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalFlowDoesNotExistsException;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalFlowExistsException;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalFlowStepDoesNotExistsException;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

class ProcessApproval implements ProcessApprovalContract
{
    public function __construct()
    {
    }

    public function flows(): \Illuminate\Database\Eloquent\Collection|array
    {
        return ProcessApprovalFlow::query()->with('steps.role')->get();
    }

    public function flowsWithSteps(): \Illuminate\Database\Eloquent\Collection|array
    {
        return  ProcessApprovalFlow::query()->with(['steps', 'steps.role'])->whereHas('steps')->get();
    }

    public function steps(): \Illuminate\Database\Eloquent\Collection|array
    {
        return ProcessApprovalFlowStep::query()->with('role')->get();
    }

    public function createFlow(string $name, string $modelClass): ProcessApprovalFlow
    {
        if (!Str::contains($name, '\\')) {
            $name = config('process_approval.models_path') . "\\{$name}";
        }
        if (class_exists($name)) {
            try {
                ProcessApproval::createFlow(
                    name: Str::of($name)->afterLast('\\')->snake(' ')->title()->toString(),
                    modelClass: get_class(new $name())
                );
                info("{$name} created successfully!");
            } catch (\Exception $e) {
                echo "Failed to create Flow: " . $e->getMessage();
            }
        } else {
            echo "The model `{$name}` you specified doesn't exist";
        }
        return true;
    }

    public function deleteFlow(int $flowId): bool|null
    {
        $approvalFlow = ProcessApprovalFlow::find($flowId);
        if(!$approvalFlow){
            throw ApprovalFlowDoesNotExistsException::create();
        }
        DB::beginTransaction();
        try {
            $approvalFlow->steps()->delete();
            $approvalFlow->delete();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return true;
    }

    public function deleteStep(int $stepId): ?bool
    {
        $step = ProcessApprovalFlowStep::find($stepId);
        if(!$step){
            throw ApprovalFlowStepDoesNotExistsException::create();
        }
        return $step->delete();
    }


    public function createStep(int $flowId, int $roleId, string|null $action = 'APPROVE'): ProcessApprovalFlowStep
    {
        $flow = ProcessApprovalFlow::find($flowId);
        if(!$flow){
            throw ApprovalFlowDoesNotExistsException::create();
        }
        $step = $flow->steps()->create([
            'role_id' => $roleId,
            'action' => $action,
            'active' => 1
        ]);
        return $step;
    }
}
