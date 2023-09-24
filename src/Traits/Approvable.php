<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Events\ApprovalNotificationEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovalCompletedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessDiscardedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessRejectedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessSubmittedEvent;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalStatus;

trait Approvable
{
    private Collection|null $_approvalSteps = null;


    protected static function boot()
    {
        parent::boot();
        static::created(static function ($model) {
            $model->approvalStatus()->create([
                'steps' => $model->approvalFlowSteps()->map(function ($item) {
                    $set = $item->only(['id', 'role_id', 'action']);
                    $set['process_approval_id'] = null;
                    $set['process_approval_action'] = null;
                    return $set;
                }),
                'status' => (property_exists($model, 'autoSubmit') && $model->autoSubmit) ? ApprovalStatusEnum::SUBMITTED->value : ApprovalStatusEnum::CREATED->value,
            ]);
        });
    }

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

    public function approvalStatus(): MorphOne
    {
        return $this->morphOne(ProcessApprovalStatus::class, 'approvable');
    }

    /**
     * Approvals relation
     * @return MorphMany
     */
    public function approvals(): MorphMany
    {
        return $this->morphMany(ProcessApproval::class, 'approvable');
    }

    /**
     * Last approval relation
     * @return MorphOne
     */
    public function lastApproval(): MorphOne
    {
        return $this->morphOne(ProcessApproval::class, 'approvable')->latest();
    }

    public function approvalFlowSteps()
    {
        return ProcessApprovalFlowStep::query()
            ->join('process_approval_flows', 'process_approval_flows.id', 'process_approval_flow_steps.process_approval_flow_id')
            ->where('process_approval_flows.approvable_type', self::getApprovableType())
            ->select('process_approval_flow_steps.*')
            ->orderByRaw('`order` asc, id asc')
            ->get();
    }


    public static function approved(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::APPROVED->value);
        });
    }

    public static function rejected(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::REJECTED->value);
        });
    }

    /**
     * @return Builder
     */
    public static function discarded(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::DISCARDED->value);
        });
    }

    /**
     * Load approvals for the model
     * @return Collection|void|null
     */
    private function loadApprovals()
    {
        if ($this->_approvalSteps !== null) {
            return $this->_approvalSteps;
        }
    }

    /**
     * Check if Approval process is completed
     * @return bool
     */
    public function isApprovalCompleted(): bool
    {
        foreach (collect($this->approvalStatus()->steps ?? []) as $index => $item) {
            if ($item->approval_action === null || $item->process_approval_id === null) {
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
        return !in_array($this->approvalStatus->status, [ApprovalStatusEnum::CREATED->value, ApprovalStatusEnum::SUBMITTED->value, ApprovalStatusEnum::PENDING->value,], true);
    }

    /**
     * Check if approval has started
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->approvalStatus?->status !== ApprovalStatusEnum::CREATED->value;
    }

    /**
     * Get the next approval Step
     * @return ProcessApprovalFlowStep|null
     */
    public function nextApprovalStep(): ProcessApprovalFlowStep|null
    {
        foreach (collect($this->approvalStatus->steps ?? []) as $index => $step) {
            if($step['process_approval_id'] === null ){
                return ProcessApprovalFlowStep::query()->with('role')->find($step['id']);
            }
            if ($step['process_approval_action'] !== ApprovalActionEnum::APPROVED->value && $step['process_approval_action'] !== ApprovalActionEnum::DISCARDED->value) {
                return ProcessApprovalFlowStep::query()->with('role')->find($step['id']);
            }
            if ($step['process_approval_action'] === ApprovalActionEnum::DISCARDED->value) {
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
        $previous_id = null;
        foreach (collect($this->approvalStatus->steps ?? []) as $index => $step) {
            if ($step['process_approval_id'] === null) {
                return ProcessApprovalFlowStep::query()->find($previous_id);
            } else {
                $previous_id = $step->id;
            }
        }
        return null;
    }


    /**
     * It makes sense if approvable requests are edited before they are submitted for approvals
     * @return void
     */
    public function submit($user = null)
    {
        ProcessSubmittedEvent::dispatch($this);
        $nextStep = $this->nextApprovalStep();
        if (!$nextStep) {
            ApprovalNotificationEvent::dispatch('Approval already completed', $this);
            return false;
        }
        DB::beginTransaction();
        try {
            $approval = ProcessApproval::query()->updateOrCreate([
                'approvable_type' => $this->getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep->id,
                'approval_action' => ApprovalActionEnum::APPROVED,
                'comment' => '',
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            if ($approval) {
                $this->approvalStatus()->update(['status' => ApprovalStatusEnum::SUBMITTED]);
                ProcessApprovedEvent::dispatch($approval);
                if ($this->isApprovalCompleted()) {
                    if ($this->onApprovalCompleted($approval)) {
                        // Approval went well
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
        if ($this->isApprovalCompleted()) {
            ProcessApprovalCompletedEvent::dispatch($approval);
        }
        DB::commit();
        return $approval ?? false;
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
            ApprovalNotificationEvent::dispatch('Approval already completed', $this);
            return false;
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
                $this->updateStatus($nextStep->id, $approval);
                ProcessApprovedEvent::dispatch($approval);
                if ($this->isApprovalCompleted()) {
                    if ($this->onApprovalCompleted($approval)) {
                        // Approval went well
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
        if ($this->isApprovalCompleted()) {
            ProcessApprovalCompletedEvent::dispatch($approval);
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
        DB::beginTransaction();
        try {
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
                $this->updateStatus($nextStep->id, $approval);
                $this->loadApprovals();
                ProcessRejectedEvent::dispatch($approval);
            }
        } catch (\Exception $e) {
            Log::debug('Process Approval - reject: ', $e);
            DB::rollBack();
        }
        DB::commit();
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
        DB::beginTransaction();
        try {
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
                $this->updateStatus($nextStep->id, $approval);
                $this->loadApprovals();
                ProcessDiscardedEvent::dispatch($approval);
            }
        } catch (\Exception $e) {
            Log::debug('Process Approval - discard: ', $e);
            DB::rollBack();
        }
        DB::commit();
        return $approval ?? false;
    }

    /**
     * Get list of users capable of approving this request next
     * @return mixed
     */
    public function getNextApprovers(): Collection
    {
        $this->loadApprovals();
        $nextStep = $this->nextApprovalStep();
        return (config('process_approval.users_model'))::role($nextStep?->role)->get();
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
     * @return string|null
     */
    public function getViewLink(): string|null
    {
        if (method_exists($this, 'viewLink')) {
            return $this->viewLink();
        }
        return null;
    }

    public function getApprovalSummaryUI()
    {
        $html = "";
        foreach (($this->approvalStatus->steps ?? []) as $index => $item) {
            $html .= '<span class="badge bg-success-transparent" title="'.($item['process_approval_action'] ?? 'Pending').'" data-bs-toggle="tooltip">✔️</span>';
        }
        return $html;
    }

    /**
     * @param $stepId
     * @param ProcessApproval $approval
     * @return int
     */
    private function updateStatus($stepId, ProcessApproval $approval): int
    {
        $steps = collect($this->approvalStatus->steps);
        $current = $steps->map(static function($step) use($stepId, $approval) {
            if($step['id'] === $stepId){
                $step['process_approval_id'] = $approval->id;
                $step['process_approval_action'] = $approval->approval_action;
            }
            return $step;
        });
        $action = $approval->approval_action;
        if($action === ApprovalStatusEnum::APPROVED->value && !$this->isApprovalCompleted()){
            $action = ApprovalStatusEnum::PENDING->value;
        }
        return $this->approvalStatus()->update([
            'steps' => $current->toArray(),
            'status' => $action
        ]);
    }

}

