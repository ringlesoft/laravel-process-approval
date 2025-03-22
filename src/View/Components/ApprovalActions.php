<?php

namespace RingleSoft\LaravelProcessApproval\View\Components;


use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
    public Collection $modelApprovalSteps;

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


    public function render(): Closure|Htmlable|View|string
    {
        if (!count($this->modelApprovalSteps)) {
            return "";
        }
        if (Str::of(config('process_approval.css_library'))->startsWith('bootstrap')) {
            $versionNumber = Str::of(config('process_approval.css_library'))->after('bootstrap')->toString();
            if ($versionNumber === '3') {
                $bsVersion = 3;
            } else if ($versionNumber === '4') {
                $bsVersion = 4;
            } else {
                $bsVersion = 5;
            }
            $bsVersionAttribute = $this->getModalToggleAttributes($bsVersion);
            return view()->file(__DIR__ . '/../../../resources/views/components/approval-actions-bs.blade.php', compact('bsVersionAttribute'));
        }
        return view()->file(__DIR__ . '/../../../resources/views/components/approval-actions-tw.blade.php');
    }


    private function getModalToggleAttributes($bsVersion): string
    {
        if ($bsVersion === 3) {
            return '';
        } else if ($bsVersion === 4 || $bsVersion === 5) {
            return 'bs-';
        } else {
            return 'bs-'; // Default to Bootstrap 4/5 syntax
        }
    }

}
