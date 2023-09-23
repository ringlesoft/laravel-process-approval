<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessDiscardedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessRejectedEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\RequestStatusEnum;
use RingleSoft\LaravelProcessApproval\Events\ProcessSubmittedEvent;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovalCompletedEvent;

trait Approvable
{
    private Collection|null $_approvalSteps = null;

    /**
     * Get the class of this approvable
     * @return string
     */
    public static function getApprovableType(): string
    {
        return static::class;
    }

    /**
     * Get the flow model of this approvable
     * @return ProcessApprovalFlow|Builder|null
     */
    public static function approvalFlow(): ProcessApprovalFlow|Builder|null
    {
        return ProcessApprovalFlow::query()->where('approvable_type', self::getApprovableType())->with('steps.approval')->first();
    }


    public static function approved(): Builder {
        return self::query()->whereHas('lastApproval', static function($q) {
            return $q->where('approval_action', ApprovalActionEnum::APPROVED->value)
                ->whereRaw("process_approvals.id = (select MAX(a2.id) from process_approvals as a2 WHERE a2.approvable_id = process_approvals.approvable_id AND a2.approvable_type = process_approvals.approvable_type LIMIT 1 )");
        });
    }

    public static function rejected(): Builder {
        return self::query()->whereHas('lastApproval', static function($q) {
            return $q->where('approval_action', ApprovalActionEnum::REJECTED->value)
                ->whereRaw("process_approvals.id = (select MAX(a2.id) from process_approvals as a2 WHERE a2.approvable_id = process_approvals.approvable_id AND a2.approvable_type = process_approvals.approvable_type LIMIT 1 )");
        });
    }

    public static function discarded(): Builder {
        return self::query()->whereHas('lastApproval', static function($q) {
            return $q->where('approval_action', ApprovalActionEnum::DISCARDED->value)
                ->whereRaw("process_approvals.id = (select MAX(a2.id) from process_approvals as a2 WHERE a2.approvable_id = process_approvals.approvable_id AND a2.approvable_type = process_approvals.approvable_type LIMIT 1 )");
        });
    }

    /**
     * It makes sense if approvable requests are edited before they are submitted for approvals
     * @return void
     */
    public function submit($user = null)
    {
        ProcessSubmittedEvent::dispatch($this);
        if($this->getApprovalSteps()->count() === 0) {
            $this->completeApproval();
        }
    }

    private function loadApprovals()
    {
        if ($this->_approvalSteps !== null) {
            return $this->_approvalSteps;
        }
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(ProcessApproval::class, 'approvable');
    }

    public function lastApproval(): MorphOne
    {
        return $this->morphOne(ProcessApproval::class, 'approvable')->latest();
    }

    /**
     * Returns a collection of steps with corresponding approvals
     * @return mixed
     */
    private function getApprovalSteps(): Collection
    {
        $this->loadApprovals();
        $id = $this->id;
        $myClass =  (string) self::getApprovableType();
        $this->_approvalSteps = ProcessApprovalFlowStep::query()
            ->join('process_approval_flows', 'process_approval_flow_steps.process_approval_flow_id', '=', 'process_approval_flows.id')
            ->leftJoin('process_approvals', static function ($join) use ($id, $myClass) {
                $join->on('process_approval_flow_steps.id', '=', 'process_approvals.process_approval_flow_step_id')
                    ->on('process_approvals.approvable_type', '=', DB::raw("'". (addslashes($myClass)) ."'"))
                    ->on('process_approvals.approvable_id', '=', DB::raw("'{$id}'"))
                    ->whereRaw("process_approvals.id = (select MAX(a2.id) from process_approvals as a2 WHERE a2.process_approval_flow_step_id = process_approval_flow_steps.id AND a2.approvable_id = {$id} AND a2.approvable_type = '".(addslashes($myClass))."' LIMIT 1 )");
            })
            ->orderByRaw('process_approval_flow_steps.order asc, process_approval_flow_steps.id asc')
            ->where('process_approval_flow_steps.active', 1)
            ->where('process_approval_flows.approvable_type', (string)$myClass)
            ->selectRaw('process_approval_flow_steps.*, process_approvals.approval_action, process_approvals.user_id')
            ->get();
        return $this->_approvalSteps;
    }

    /**
     * Check if Approval process is completed
     * @return bool
     */
    public function isApprovalCompleted(): bool
    {
        foreach ($this->getApprovalSteps() ?? [] as $index => $item) {
            if ($item->approval_action !== ApprovalActionEnum::APPROVED->value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if this request is rejected
     * @return bool
     */
    public function isRejected(): bool
    {
        $next = $this->nextApprovalStep();
        return $next->approval?->approval_action === ApprovalActionEnum::REJECTED->value;

    }

    /**
     * Check if approval has started
     * @return bool
     */
    public function isApprovalStarted(): bool
    {
        return $this->approvals()->count() > 0;
    }

    /**
     * Get the next approval Step
     * @return ProcessApprovalFlowStep|null
     */
    public function nextApprovalStep(): ProcessApprovalFlowStep|null
    {
        foreach ($this->getApprovalSteps() ?? [] as $index => $item) {
            if ($item->approval_action !== ApprovalActionEnum::APPROVED->value && $item->approval_action !== ApprovalActionEnum::DISCARDED->value) {
                return ProcessApprovalFlowStep::query()->with('role')->find($item->id);
            }
            if ($item->approval_action === ApprovalActionEnum::DISCARDED->value) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the previous Approval Step
     * @return ProcessApprovalFlowStep|null
     */
    public function previousApprovalStep(): ProcessApprovalFlowStep|null
    {
        $steps = $this->getApprovalSteps() ?? [];
        foreach ($steps as $index => $item) {
            if ($steps[$index + 1]->approval_action === null) {
                return ProcessApprovalFlowStep::query()->find($item->process_approval_flow_step_id);
            }
        }
        return null;
    }

    /**
     * Approve a request
     * @param null $comment
     * @param Authenticatable|null $user
     * @return false|Builder|Model
     */
    public function approve($comment = null, Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse // TODO remove the redirectResponse
    {
        $nextStep = $this->nextApprovalStep();
        if (!$nextStep) {
            session()->flash('error', 'No approval steps remaining');
            return redirect()->back(); // TODO throw exception and capture it
        }
        DB::beginTransaction();
        try {
            $approval = ProcessApproval::query()->updateOrCreate([
                'approvable_type' => $this->getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep->id,
                'approval_action' => ApprovalActionEnum::APPROVED,
                'comment' => $comment,
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            if ($approval) {
                ProcessApprovedEvent::dispatch($approval);
                if ($this->isApprovalCompleted()) {
                    $this->update(['status' => RequestStatusEnum::APPROVED->value]);
                    if ($this->onApprovalCompleted($approval)) {
                        Log::info('Approval went well');
                    } else {
                        throw new \Exception('Callback action after approval failed');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Process approval failure: ', [$e]);
            DB::rollBack();
            return false;
        }
        DB::commit();
        return $approval ?? false;
    }


    /**
     * Reject a request
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool
     */
    public function reject($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        $rules = [];
        // TODO run a validation
        $nextStep = $this->nextApprovalStep();
        $approval = ProcessApproval::query()->create([
            'approvable_type' => $this->getApprovableType(),
            'approvable_id' => $this->id,
            'process_approval_flow_step_id' => $nextStep->id,
            'approval_action' => ApprovalActionEnum::REJECTED,
            'comment' => $comment,
            'user_id' => $user?->id,
            'approver_name' => $user?->name ?? 'Unknown'
        ]);
        if ($approval) {
            $this->loadApprovals();
            ProcessRejectedEvent::dispatch($approval);
        }
        return $approval ?? false;
    }

    /**
     * Discard a request
     * @param $comment
     * @param Authenticatable|null $user
     * @return ApprovableModel|bool
     */
    public function discard($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        $nextStep = $this->nextApprovalStep();
        $approval = ProcessApproval::query()->create([
            'approvable_type' => $this->getApprovableType(),
            'approvable_id' => $this->id,
            'process_approval_flow_step_id' => $nextStep->id,
            'approval_action' => ApprovalActionEnum::DISCARDED,
            'comment' => $comment,
            'user_id' => $user?->id,
            'approver_name' => $user?->name ?? 'Unknown'
        ]);
        if ($approval) {
            $this->loadApprovals();
            ProcessDiscardedEvent::dispatch($approval);
        }
        return $approval ?? false;
    }

    /**
     * Get list of users capable of approving this request next
     * @return mixed
     */
    public function getNextApprovers(): Collection
    {
        $this->loadApprovals(); // Refresh
        $nextStep = $this->nextApprovalStep();
        return User::role($nextStep?->role)->get();
    }

    /**
     * Check if this request can be approved by a user
     * @param Authenticatable|null $user
     * @return bool
     */
    public function canBeApprovedBy(Authenticatable|null $user): bool|null
    {
        $nextStep = $this->nextApprovalStep();
        return $nextStep && $user?->hasRole($nextStep->role);
    }

    /**
     * The link for viewing the request
     * Always override this method for accuracy
     * @return string
     */
    public function getViewLink(): string
    {
        $name = Str::of(self::getApprovableType())->afterLast("\\")->snake()->plural();
        return route('admin.' . $name->toString() . '.show', $this);
    }

    public function getApprovalSummaryUI()
    {
        return $this->isApprovalCompleted() ?
            '<span class="badge bg-success-transparent" title="Approved" data-bs-toggle="tooltip"><i class="bi bi-check"></i></span>'
            :
            '<span class="badge bg-warning-transparent" title="Pending" data-bs-toggle="tooltip"><i class="bi bi-clock"></i></span>';
    }

}

