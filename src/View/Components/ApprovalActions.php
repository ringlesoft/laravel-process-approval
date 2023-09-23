<?php

namespace RingleSoft\LaravelProcessApproval\View\Components;


use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ApprovalActions extends Component
{
    public Builder|ProcessApprovalFlow|null $approvalFlow;
    public ProcessApprovalFlowStep|null $nextApprovalStep;
    public bool $userCanApprove;

    public function __construct(public ApprovableModel $model)
    {
        $this->approvalFlow = $model->approvalFlow();
        $this->nextApprovalStep = $model->nextApprovalStep();
        $this->userCanApprove = $model->canBeApprovedBy(Auth::user());
    }


    public function render(): View
    {
        return view('ringlesoft::components.approval-actions');
    }
}
