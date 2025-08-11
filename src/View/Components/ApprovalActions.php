<?php

namespace RingleSoft\LaravelProcessApproval\View\Components;


use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

class ApprovalActions extends Component
{
    public ProcessApprovalFlowStep|null $nextApprovalStep;
    public bool $userCanApprove;
    public Collection $modelApprovalSteps;

    public function __construct(public ApprovableModel $model)
    {
        $modelApprovals = $model->approvals()->with('processApprovalFlowStep')->get()->keyBy('process_approval_flow_step_id');
        $this->modelApprovalSteps = ProcessApprovalFlowStep::query()
            ->whereIntegerInRaw('id', collect($model->approvalStatus->steps ?? [])->pluck('id')->toArray())
            ->with('role')
            ->orderBy('order')
            ->get()
        ->map(function($step) use($modelApprovals) {
            $step->approval = $modelApprovals->get($step->id);
            return $step;
        });
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
        if (Str::of(config('process_approval.css_library'))->startsWith('tailwind')) {
            return view()->file(__DIR__ . '/../../../resources/views/components/approval-actions-tw.blade.php');
        }
        return view()->file(__DIR__ . '/../../../resources/views/components/approval-actions.blade.php');
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
