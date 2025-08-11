<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\DataObjects\ApprovalStatusStepData;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovalCompletedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessDiscardedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessRejectedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessReturnedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessSubmittedEvent;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalCompletedCallbackFailedException;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalsPausedException;
use RingleSoft\LaravelProcessApproval\Exceptions\NoFurtherApprovalStepsException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestAlreadySubmittedException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestNotSubmittedException;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalStatus;
use RingleSoft\LaravelProcessApproval\View\Components\ApprovalStatusSummary;
use RuntimeException;
use Throwable;

/**
 * @mixin ApprovableModel
 * @property bool approvalsPaused
 */
trait Approvable
{
    private Collection|null $_approvalSteps = null;


    protected static function bootApprovable(): void
    {
        static::created(static function ($model) {
            if (method_exists($model, 'bypassApprovalProcess') && $model->bypassApprovalProcess()) {
                return;
            }
            $model->approvalStatus()->create([
                'steps' => $model->approvalFlowSteps()->map(function ($item) {
                    return $item->toApprovalStatusArray();
                }),
                'status' => ((property_exists($model, 'autoSubmit') && $model->autoSubmit) || (method_exists($model, 'enableAutoSubmit') && $model->enableAutoSubmit())) ? ApprovalStatusEnum::SUBMITTED->value : ApprovalStatusEnum::CREATED->value,
                'creator_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Initialize the trait.
     */
    protected function initializeApprovable(): void
    {
        /**
         * Eager loading the relation to improve performance
         */
        $this->with = array_merge($this->with ?? [], ['approvalStatus']);
    }


    /**
     * Bypass the approval process for this model instance
     * @return bool
     */
    public function bypassApprovalProcess(): bool
    {
        return false;
    }

    /**
     * Enable auto-submit for this model instance
     * @return bool
     */
    public function enableAutoSubmit(): bool
    {
        return false;
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
     * @return ProcessApprovalFlow|null
     */
    public static function approvalFlow(): ProcessApprovalFlow|null
    {
        return ProcessApprovalFlow::query()
            ->where('approvable_type', self::getApprovableType())
            ->with('steps.role')
            ->first();
    }

    /**
     * Check if this Model requires approval
     * @return bool
     */
    public static function requiresApproval(): bool
    {
        $flow = static::approvalFlow();
        if (!$flow) {
            return false;
        }
        return $flow->steps->count() > 0;
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
        return $this->morphOne(ProcessApproval::class, 'approvable')
            ->where('approval_action', '!=', ApprovalStatusEnum::RETURNED->value)
            ->latest();
    }

    public function approvalFlowSteps(): array|Collection
    {
        return ProcessApprovalFlowStep::query()
            ->join('process_approval_flows', 'process_approval_flows.id', 'process_approval_flow_steps.process_approval_flow_id')
            ->where('process_approval_flows.approvable_type', self::getApprovableType())
            ->select('process_approval_flow_steps.*')
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
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
     * @return Builder
     */
    public static function returned(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::RETURNED->value);
        });
    }

    /**
     * @return Builder
     */
    public static function nonSubmitted(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::CREATED->value);
        });
    }

    /**
     * @return Builder
     */
    public static function submitted(): Builder
    {
        return self::query()->whereHas('approvalStatus', static function ($q) {
            return $q->where('status', ApprovalActionEnum::SUBMITTED->value);
        });
    }


    /**
     * @param ProcessApprovalFlowStep $step
     * @return Builder
     * @removed This method is not fully functional. Reserved for future updates
     */
    public static function waitingForStep(ProcessApprovalFlowStep $step): Builder
    {
        // Getting Steps before
        $stepsBefore = ProcessApprovalFlowStep::query()
            ->where('process_approval_flow_id', $step->process_approval_flow_id)
            ->where('id', '!=', $step->id)
            ->when(($step->order === null), function ($q) use ($step) {
                $q->where('id', '<', $step->id);
            })
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        return self::query()
            ->whereHas('approvalStatus', static function ($q) use ($step, $stepsBefore) {
                $q->where('status', '!=', ApprovalActionEnum::APPROVED->value)
                    ->where('status', '!=', ApprovalActionEnum::CREATED->value);
                foreach ($stepsBefore as $stepBefore) {
                    $q->whereJsonContains('steps', [
                        'id' => $stepBefore->id,
                        'process_approval_action' => ApprovalActionEnum::APPROVED->value,
                    ]);
                }
                return $q->where(function ($q2) use ($step) {
                    $q2->whereJsonContains('steps', [
                        'id' => $step->id,
                        'process_approval_id' => null,
                    ])->orWhere(function ($q3) use ($step) {
                        $q3->whereJsonDoesntContain('steps', [
                            'id' => $step->id,
                            'process_approval_id' => null,
                        ])->whereJsonContains('steps', [
                            'id' => $step->id,
                            'process_approval_action' => ApprovalActionEnum::RETURNED->value,
                        ]);
                    });
                });
            });
    }

    /**
     * Check if Approval process is completed
     * @param array|null $currentSteps
     * @return bool
     */
    public function isApprovalCompleted(array $currentSteps = null): bool
    {
        $registeredSteps = $currentSteps ? collect($currentSteps) : collect($this->approvalStatus->steps ?? []);
        if ($registeredSteps->count() > 0) {
            foreach ($registeredSteps as $item) {
                if ($item['process_approval_action'] === null || $item['process_approval_id'] === null || $item['process_approval_action'] === ApprovalStatusEnum::RETURNED->value) {
                    return false;
                }
            }
            return $registeredSteps->last()['process_approval_action'] !== ApprovalActionEnum::REJECTED->value;
        }
        return true;
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
     * Check if this request is rejected
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->approvalStatus?->status === ApprovalStatusEnum::REJECTED->value;

    }

    /**
     * Check if this request is discarded
     * @return bool
     */
    public function isDiscarded(): bool
    {
        return $this->approvalStatus?->status === ApprovalStatusEnum::DISCARDED->value;

    }

    public function isReturned(): bool
    {
        return $this->approvalStatus?->status === ApprovalStatusEnum::RETURNED->value;
    }

    public function isPending(): bool
    {
        return $this->approvalStatus?->status === ApprovalStatusEnum::PENDING->value;
    }

    /**
     * Check if approval has started
     * @return bool
     */
    public function isApprovalStarted(): bool
    {
        return !in_array($this->approvalStatus->status, [ApprovalStatusEnum::CREATED->value, ApprovalStatusEnum::SUBMITTED->value], true);
    }

    private function nextApprovalStepId(): ?int
    {
        foreach (collect($this->approvalStatus->steps ?? []) as $step) {
            // Break when the first discarded step is found (approval should not go further)
            if ($step['process_approval_action'] === ApprovalActionEnum::DISCARDED->value) {
                return null;
            }
            // Not yet approved or was returned to this step
            if (
                ($step['process_approval_id'] === null || $step['process_approval_action'] === ApprovalStatusEnum::RETURNED->value) ||
                ($step['process_approval_action'] !== ApprovalActionEnum::APPROVED->value)
            ) {
                return $step['id'];
            }
        }
        return null;
    }

    /**
     * Get the next approval Step
     * @return ProcessApprovalFlowStep|null
     */
    public function nextApprovalStep(): ProcessApprovalFlowStep|null
    {
        if ($nextStepId = $this->nextApprovalStepId()) {
            return ProcessApprovalFlowStep::query()->with('role')->find($nextStepId);
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
        foreach (collect($this->approvalStatus->steps ?? []) as $step) {
            if ($step['process_approval_id'] === null || $step['process_approval_action'] === ApprovalStatusEnum::RETURNED->value) {
                return ProcessApprovalFlowStep::query()->find($previous_id);
            }
            $previous_id = $step['id'];
        }
        return null;
    }


    /**
     * It makes sense if approvable requests are edited before they are submitted for approvals
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool|RedirectResponse
     * @throws RequestAlreadySubmittedException
     * @throws Throwable
     */
    public function submit(Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse
    {
        if ($this->isSubmitted()) {
            throw RequestAlreadySubmittedException::create($this);
        }
        if ($this->approvalStatus->creator_id && $this->approvalStatus->creator_id !== Auth::id()) {
            throw new RuntimeException('Only the creator can submit the record');
        }
        $user = $user ?? Auth::user();
        try {
            DB::beginTransaction();
            $approval = ProcessApproval::query()->create([
                'approvable_type' => self::getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $this->approvalFlowSteps()?->first()?->id ?? null, // Backward compatibility
                'approval_action' => ApprovalActionEnum::SUBMITTED->value,
                'comment' => '',
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            $this->approvalStatus()->update(['status' => ApprovalStatusEnum::SUBMITTED]);
            if ($this->isApprovalCompleted()) {
                if (method_exists($this, 'onApprovalCompleted') && $this->onApprovalCompleted($approval)) {
                    // Approval went well, no need to rollback
                } else {
                    throw new RuntimeException('Callback action after approval failed');
                }
            }
            DB::commit();
            if ($approval) {
                ProcessSubmittedEvent::dispatch($this);
            }
            return $approval;
        } catch (Throwable $e) {
            Log::debug('Process approval failure: ', [$e]);
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Approve a request
     * @param null $comment
     * @param Authenticatable|null $user
     * @return false|Builder|Model
     * @throws NoFurtherApprovalStepsException|ApprovalCompletedCallbackFailedException|RequestNotSubmittedException|ApprovalsPausedException|Throwable
     */
    public function approve($comment = null, Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse // TODO remove the redirectResponse
    {
        if (!$this->isSubmitted()) {
            throw RequestNotSubmittedException::create($this);
        }
        $nextStep = $this->nextApprovalStep();
        if (!$nextStep) {
            throw NoFurtherApprovalStepsException::create($this);
        }
        if ($this->approvalsPaused) {
            throw ApprovalsPausedException::create($this);
        }
        $user = $user ?? Auth::user();
        try {
            DB::beginTransaction();
            $approval = ProcessApproval::query()->updateOrCreate([
                'approvable_type' => self::getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep->id,
                'approval_action' => ApprovalActionEnum::APPROVED,
                'comment' => $comment,
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            if ($approval) {
                $this->updateStatus($nextStep->id, $approval);
                if ($this->refresh()->isApprovalCompleted()) {
                    try {
                        $approvalCompleted = $this->onApprovalCompleted($approval);
                    } catch (Exception $e) {
                        throw ApprovalCompletedCallbackFailedException::create($this, $e->getMessage());
                    }
                    if (!$approvalCompleted) {
                        throw ApprovalCompletedCallbackFailedException::create($this);
                    }
                }
            }
            DB::commit();
            if ($approval) {
                ProcessApprovedEvent::dispatch($approval);
                if ($this->isApprovalCompleted()) {
                    ProcessApprovalCompletedEvent::dispatch($this);
                }
            }
            return $approval;
        } catch (Throwable $e) {
            Log::error('Process approval failure: ', [$e]);
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Reject a request
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool
     * @throws Throwable
     */
    public function reject($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        if (!$this->isSubmitted()) {
            throw RequestNotSubmittedException::create($this);
        }
        if ($this->approvalsPaused) {
            throw ApprovalsPausedException::create($this);
        }
        $user = $user ?? Auth::user();
        try {
            DB::beginTransaction();
            $nextStep = $this->nextApprovalStep();
            $approval = ProcessApproval::query()->create([
                'approvable_type' => self::getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep?->id,
                'approval_action' => ApprovalActionEnum::REJECTED,
                'comment' => $comment,
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            DB::commit();
            if ($approval) {
                $this->updateStatus($nextStep?->id, $approval);
                ProcessRejectedEvent::dispatch($approval);
            }
            return $approval ?? false;
        } catch (Throwable $e) {
            Log::error('Process Approval - reject: ', [$e]);
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Discard a request
     * @param $comment
     * @param Authenticatable|null $user
     * @return ApprovableModel|bool
     * @throws Throwable
     */
    public function discard($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        if (!$this->isSubmitted()) {
            throw RequestNotSubmittedException::create($this);
        }
        if ($this->approvalsPaused) {
            throw ApprovalsPausedException::create($this);
        }
        $user = $user ?? Auth::user();
        $nextStep = $this->nextApprovalStep();
        DB::beginTransaction();
        try {
            $approval = ProcessApproval::query()->create([
                'approvable_type' => self::getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep?->id,
                'approval_action' => ApprovalActionEnum::DISCARDED,
                'comment' => $comment,
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            $this->updateStatus($nextStep?->id, $approval);
            DB::commit();
            if ($approval) {
                ProcessDiscardedEvent::dispatch($approval);
            }
            return $approval ?? false;
        } catch (Exception $e) {
            Log::error('Process Approval - discard: ', [$e]);
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Send the record back to the previous step or a specific step
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool
     * @throws RequestNotSubmittedException
     * @throws Throwable
     */
    public function return($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        if (!$this->isSubmitted()) {
            throw RequestNotSubmittedException::create($this);
        }
        if ($this->approvalsPaused) {
            throw ApprovalsPausedException::create($this);
        }
        $user = $user ?? Auth::user();
        $previousStep = $this->previousApprovalStep();
        $nextStep = $this->nextApprovalStep();
        try {
            DB::beginTransaction();
            $approval = ProcessApproval::query()->create([
                'approvable_type' => self::getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $nextStep?->id,
                'approval_action' => ApprovalActionEnum::RETURNED,
                'comment' => $comment,
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            if ($previousStep) {
                $flag = false;
                $approvalStatusSteps = ApprovalStatusStepData::collectionFromProcessApprovalStatus($this->approvalStatus);
                $approvalStatusSteps->map(function (ApprovalStatusStepData $item) use ($previousStep, &$flag) {
                    if ($item->belongsToStep($previousStep->id)) {
                        $item->makeReturned();
                        $flag = true;
                    } else if ($flag && $item->isReturned()) {
                        $item->reset();
                    }
                    return $item;
                });
                unset($flag);

                $this->approvalStatus()->update([
                    'steps' => ApprovalStatusStepData::collectionToArray($approvalStatusSteps),
                    'status' => ApprovalStatusEnum::RETURNED->value,
                ]);
            } else {
                $this->approvalStatus()->update([
                    'status' => ApprovalStatusEnum::CREATED->value,
                ]);
            }
            DB::commit();
            if ($approval) {
                Event::dispatch(new ProcessReturnedEvent($approval));
            }
            return $approval;
        } catch (Throwable $e) {
            Log::error('Process Approval - return: ', [$e]);
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Get list of users capable of approving this request next
     * @return mixed
     */
    public function getNextApprovers(): Collection
    {
        $nextStep = $this->nextApprovalStep();
        return (config('process_approval.users_model'))::role($nextStep?->role)->get();
    }

    /**
     * Check if this request can be approved by a user
     * @param Authenticatable|null $user
     * @return bool|null
     */
    public function canBeApprovedBy(Authenticatable|null $user): bool|null
    {
        $nextStep = $this->nextApprovalStep();
        return !$this->approvalsPaused && $this->isSubmitted() && $nextStep && $user?->hasRole($nextStep->role);
    }

    /**
     * Check if the request can be submitted by a user
     * @param Authenticatable $user
     * @return bool|null
     */
    public function canBeSubmittedBy(Authenticatable $user): bool|null
    {
        return !$this->isSubmitted() && ($this->approvalStatus->creator_id === null || $this->approvalStatus->creator_id === $user->id);
    }


    /**
     * @return void
     * @throws Throwable
     */
    public function undoLastApproval(): void
    {
        $lastApproval = $this->approvals()->latest()->latest('id')->get()->first();
        if ($lastApproval) {
            try {
                DB::beginTransaction();
                $lastApproval->delete();
                $statusesCollection = ApprovalStatusStepData::collectionFromApprovable($this);
                $updatedStatuses = $statusesCollection->map(function (ApprovalStatusStepData $item) use ($lastApproval) {
                    if ($item->belongsToApproval($lastApproval)) {
                        $item->reset();
                    }
                    return $item;
                });
                $lastDoneStatus = $updatedStatuses->filter(function (ApprovalStatusStepData $item) {
                    return $item->isDone();
                })?->last();
                if ($lastDoneStatus) {
                    $newStatus = ApprovalStatusEnum::PENDING->value;
                } else {
                    $newStatus = ApprovalStatusEnum::SUBMITTED->value;
                }
                $this->approvalStatus()->update(['steps' => ApprovalStatusStepData::collectionToArray($updatedStatuses), 'status' => $newStatus]);// Todo Improve
                DB::commit();
            } catch (Throwable $e) {
                Log::error('Process Approval - discard: ', [$e]);
                DB::rollBack();
            }
        }
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

    /**
     * @param $showRole
     * @return string
     * @deprecated Use the ui component `<x-ringlesoft-approval-status-summary>` instead
     */
    public function getApprovalSummaryUI($showRole = false): string
    {
        // This is only for backwards compatibility.
        // The component `x-ringlesoft-approval-status-summary` should be used instead
        $component = new ApprovalStatusSummary($this, $showRole);
        return ($component)
            ->render()
            ->with('showRole', $showRole)
            ->with('steps', $component->steps)
            ->with('map', $component->map)
            ->toHtml();
    }

    /**
     * @param $stepId
     * @param ProcessApproval $approval
     * @return int
     */
    private function updateStatus($stepId, ProcessApproval $approval): int
    {
        $steps = ApprovalStatusStepData::collectionFromProcessApprovalStatus($this->approvalStatus);
        $current = $steps->map(static function (ApprovalStatusStepData $step) use ($stepId, $approval) {
            if ($step->belongsToStep($stepId)) {
                return $step->updateApproval($approval);
            }
            return $step;
        });
        $action = $approval->approval_action;
        if (is_a($action, ApprovalActionEnum::class)) {
            $action = $action->value;
        }
        $currentSteps = ApprovalStatusStepData::collectionToArray($current);
        if ($action === ApprovalStatusEnum::APPROVED->value && !$this->isApprovalCompleted($currentSteps)) {
            $action = ApprovalStatusEnum::PENDING->value;
        }
        return $this->approvalStatus()->update([
            'steps' => $currentSteps,
            'status' => $action
        ]);
    }


    public function getCreatorAttribute()
    {
        return $this->morphToMany(
            config('process_approval.users_model'),
            'approvable',
            'process_approval_statuses',
            'approvable_id',
            'creator_id',
            'id'
        )->latest()?->first();
    }

    /**
     * Enables pausing the approval process for intermediate actions
     * @return mixed
     */
    public function getApprovalsPausedAttribute(): mixed
    {
        if (method_exists($this, 'pauseApprovals')) {
            return $this->pauseApprovals();
        }
        return false;
    }

    /**
     * Create approval flow for this record
     * @param array|null $steps lit of roles that should be used as approval steps
     * @param string|null $name Name of the flow
     * @return  bool
     * @throws Exception
     */
    public static function makeApprovable(array|null $steps = null, string|null $name = null): bool
    {
        $processApproval = new \RingleSoft\LaravelProcessApproval\ProcessApproval();
        try {
            DB::BeginTransaction();
            $flow = $processApproval->createFlow($name ?? Str::title(self::class), self::class);
            if ($steps && count($steps) > 0) {
                $rolesModel = config('process_approval.roles_model');
                foreach ($steps as $key => $step) {
                    if (is_numeric($key) && is_numeric($step)) { // Associative
                        $roleId = ($rolesModel)::find($step)?->id;
                        $approvalActionType = ApprovalTypeEnum::APPROVE->value;
                    } elseif (is_numeric($key) && is_array($step)) { // Associative
                        $roleId = ($rolesModel)::find($step['role_id'])?->id;
                        $approvalActionType = ApprovalTypeEnum::from($step['action'])->value ?? ApprovalTypeEnum::APPROVE->value;
                    } else {
                        $roleId = ($rolesModel)::where((is_numeric($key) ? 'id' : 'name'), $key)->first()?->id;
                        $approvalActionType = ApprovalTypeEnum::from($step)->value ?? ApprovalTypeEnum::APPROVE->value;
                    }
                    if ($roleId) {
                        $processApproval->createStep($flow->id, $roleId, $approvalActionType);
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        return true;
    }

}

