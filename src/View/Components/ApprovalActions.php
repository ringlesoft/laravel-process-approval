<?php

namespace RingleSoft\LaravelProcessApproval\View\Components;


use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ApprovalActions extends Component
{
    public Builder|ProcessApprovalFlow|null $approvalFlow;
    public ProcessApprovalFlowStep|null $nextApprovalStep;
    public bool $userCanApprove;
    public \Illuminate\Support\Collection $modelApprovalSteps;

    public function __construct(public ApprovableModel $model)
    {
        $this->modelApprovalSteps = collect($model->approvalStatus->steps ?? [])->map(function ($step) {
            $step['step'] = ProcessApprovalFlowStep::with('role')->find($step['id']);
            $step['approval'] = ($step['process_approval_id'] !== null) ? ProcessApproval::find($step['process_approval_id']) : null;

            return $step;
        });
        $this->approvalFlow = $model->approvalFlow();
        $this->nextApprovalStep = $model->nextApprovalStep();
        $this->userCanApprove = $model->canBeApprovedBy(Auth::user());

    }


    public function render(): View
    {
        if (config('process_approval.css_library') === 'bootstrap') {
            return view('ringlesoft::components.approval-actions-bs');
        }
        return view('ringlesoft::components.approval-actions-tw');
    }
}
