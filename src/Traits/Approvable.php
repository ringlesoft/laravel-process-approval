<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalCompletedCallbackFailedException;
use RingleSoft\LaravelProcessApproval\Exceptions\NoFurtherApprovalStepsException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestAlreadySubmittedException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestNotSubmittedException;
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
                    $set['role_name'] = $item->role?->name ?? $item->role_id;
                    $set['process_approval_action'] = null;
                    return $set;
                }),
                'status' => (property_exists($model, 'autoSubmit') && $model->autoSubmit) ? ApprovalStatusEnum::SUBMITTED->value : ApprovalStatusEnum::CREATED->value,
                'creator_id' => Auth::id(),
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
     * Check if Approval process is completed
     * @return bool
     */
    public function isApprovalCompleted(): bool
    {
        $registeredSteps = collect($this->approvalStatus->steps ?? []);
        foreach ($registeredSteps as $index => $item) {
            if ($item['process_approval_action'] === null || $item['process_approval_id'] === null) {
                return false;
            }
        }
        return $registeredSteps->last()['process_approval_action'] !== ApprovalActionEnum::REJECTED->value;
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
        return $this->approvalStatus?->status === ApprovalActionEnum::REJECTED->value;

    }

    /**
     * Check if this request is discarded
     * @return bool
     */
    public function isDiscarded(): bool
    {
        return $this->approvalStatus?->status === ApprovalActionEnum::DISCARDED->value;

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
     * Get the next approval Step
     * @return ProcessApprovalFlowStep|null
     */
    public function nextApprovalStep(): ProcessApprovalFlowStep|null
    {
        foreach (collect($this->approvalStatus->steps ?? []) as $index => $step) {
            if ($step['process_approval_id'] === null) {
                if ($realStep = ProcessApprovalFlowStep::query()->with('role')->find($step['id'])) {
                    return $realStep;
                }
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
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool|RedirectResponse
     * @throws RequestAlreadySubmittedException
     */
    public function submit(Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse
    {
        if ($this->isSubmitted()) {
            throw RequestAlreadySubmittedException::create($this);
        }
        // TODO check if submitted by creator
        DB::beginTransaction();
        try {
            $approval = ProcessApproval::query()->updateOrCreate([
                'approvable_type' => $this->getApprovableType(),
                'approvable_id' => $this->id,
                'process_approval_flow_step_id' => $this->approvalFlowSteps()?->first()?->id ?? null, // Backward compatibility
                'approval_action' => ApprovalActionEnum::SUBMITTED->value,
                'comment' => '',
                'user_id' => $user?->id,
                'approver_name' => $user?->name ?? 'Unknown'
            ]);
            if ($approval) {
                $this->approvalStatus()->update(['status' => ApprovalStatusEnum::SUBMITTED]);
                ProcessSubmittedEvent::dispatch($this);
                ApprovalNotificationEvent::dispatch('Approvable record submitted', $this);
                if ($this->isApprovalCompleted()) {
                    if ($this->onApprovalCompleted($approval)) {
                        // Approval went well
                    } else {
                        throw new \Exception('Callback action after approval failed');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Process approval failure: ', [$e]);
            DB::rollBack();
            throw $e;
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
     * @throws NoFurtherApprovalStepsException|ApprovalCompletedCallbackFailedException|RequestNotSubmittedException
     */
    public function approve($comment = null, Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse // TODO remove the redirectResponse
    {
        if(!$this->isSubmitted()){
            throw RequestNotSubmittedException::create($this);
        }
        $nextStep = $this->nextApprovalStep();
        if (!$nextStep) {
            throw NoFurtherApprovalStepsException::create($this);
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
                        throw ApprovalCompletedCallbackFailedException::create($this);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Process approval failure: ', [$e]);
            DB::rollBack();
            throw $e;
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
     * @throws \Exception
     */
    public function reject($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        if(!$this->isSubmitted()){
            throw RequestNotSubmittedException::create($this);
        }
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
                ProcessRejectedEvent::dispatch($approval);
            }
        } catch (\Exception $e) {
            Log::error('Process Approval - reject: ', [$e]);
            DB::rollBack();
            throw $e;
        }
        DB::commit();
        return $approval ?? false;
    }

    /**
     * Discard a request
     * @param $comment
     * @param Authenticatable|null $user
     * @return ApprovableModel|bool
     * @throws \Exception
     */
    public function discard($comment = null, Authenticatable|null $user = null): ProcessApproval|bool
    {
        if(!$this->isSubmitted()){
            throw RequestNotSubmittedException::create($this);
        }
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
                ProcessDiscardedEvent::dispatch($approval);
            }
        } catch (\Exception $e) {
            Log::error('Process Approval - discard: ', [$e]);
            DB::rollBack();
            throw $e;
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
        return !$this->approvalsPaused && $this->isSubmitted() && $nextStep && $user?->hasRole($nextStep->role);
    }


    public function undoLastApproval()
    {
        $lastApproval = $this->approvals()->latest()->get()->first();
        if ($lastApproval) {
            DB::beginTransaction();
            $lastApproval->delete();
            $statusesArray = collect($this->approvalStatus->steps);
            $updatedArray = $statusesArray->map(function ($i) use ($lastApproval) {
                if ($i['process_approval_id'] == $lastApproval->id) {
                    $i['process_approval_id'] = null;
                    $i['process_approval_action'] = null;
                }
                return $i;
            });
            $this->approvalStatus()->update(['steps' => $updatedArray->toArray(), 'status' => ApprovalStatusEnum::PENDING->value]); // Todo Improve
            DB::commit();
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

    public function getApprovalSummaryUI()
    {
        $check = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m2.25 12.321 7.27 6.491c.143.127.321.19.499.19.206 0 .41-.084.559-.249l11.23-12.501c.129-.143.192-.321.192-.5 0-.419-.338-.75-.749-.75-.206 0-.411.084-.559.249l-10.731 11.945-6.711-5.994c-.144-.127-.322-.19-.5-.19-.417 0-.75.336-.75.749 0 .206.084.412.25.56" fill-rule="nonzero" fill="#fff"/></svg>';
        $rejected = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12.002 21.534c5.518 0 9.998-4.48 9.998-9.998s-4.48-9.997-9.998-9.997c-5.517 0-9.997 4.479-9.997 9.997s4.48 9.998 9.997 9.998zm0-1.5c-4.69 0-8.497-3.808-8.497-8.498s3.807-8.497 8.497-8.497 8.498 3.807 8.498 8.497-3.808 8.498-8.498 8.498zm0-6.5c-.414 0-.75-.336-.75-.75v-5.5c0-.414.336-.75.75-.75s.75.336.75.75v5.5c0 .414-.336.75-.75.75zm-.002 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z" fill-rule="nonzero" fill="#fff"/></svg>';
        $discarded = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12 10.93 5.719-5.72c.146-.146.339-.219.531-.219.404 0 .75.324.75.749 0 .193-.073.385-.219.532l-5.72 5.719 5.719 5.719c.147.147.22.339.22.531 0 .427-.349.75-.75.75-.192 0-.385-.073-.531-.219l-5.719-5.719-5.719 5.719c-.146.146-.339.219-.531.219-.401 0-.75-.323-.75-.75 0-.192.073-.384.22-.531l5.719-5.719-5.72-5.719c-.146-.147-.219-.339-.219-.532 0-.425.346-.749.75-.749.192 0 .385.073.531.219z" fill="#fff"/></svg>';
        $pending = '<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" width="12" height="12" viewBox="0 0 24 24"><path d="M12 0c6.623 0 12 5.377 12 12s-5.377 12-12 12-12-5.377-12-12 5.377-12 12-12zm0 1c6.071 0 11 4.929 11 11s-4.929 11-11 11-11-4.929-11-11 4.929-11 11-11zm0 11h6v1h-7v-9h1v8z" fill="#fff"/></svg>';
        $map = [
            'Approved' => [
                'icon' => $check,
                'color' => "rgba(50, 205, 50, 0.5)"
            ],
            'Rejected' => [
                'icon' => $rejected,
                'color' => "rgba(220, 20, 60, 0.5)"
            ],
            'Pending' => [
                'icon' => $pending,
                'color' => "rgba(255, 165, 0, 0.5)"
            ],
            'Discarded' => [
                'icon' => $discarded,
                'color' => "rgba(220, 20, 60, 0.5)"
            ],
            'Default' => [
                'icon' => $pending,
                'color' => "rgba(255, 165, 0, 0.5)"
            ]
        ];

        $html = "<div class='flex rounded'>";
        foreach (($this->approvalStatus->steps ?? []) as $index => $item) {
            $theme = $map[$item['process_approval_action'] ?? 'Default'];
            $html .= '<span class="badge" style="background-color: ' . $theme['color'] . '; padding: .1rem;" title="' . ($item['role_name'] ?? $item['role_id']) . ': ' . ($item['process_approval_action'] ?? 'Pending') . '" data-bs-toggle="tooltip">' . $theme['icon'] . '</span>';
        }
        $html .= "</div>";
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
        $current = $steps->map(static function ($step) use ($stepId, $approval) {
            if ($step['id'] === $stepId) {
                $step['process_approval_id'] = $approval->id;
                $step['process_approval_action'] = $approval->approval_action;
            }
            return $step;
        });
        $action = $approval->approval_action;
        if ($action == ApprovalStatusEnum::APPROVED->value && !$this->isApprovalCompleted()) {
            $action = ApprovalStatusEnum::PENDING->value;
        }
        return $this->approvalStatus()->update([
            'steps' => $current->toArray(),
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
        if(method_exists($this, 'pauseApprovals')){
            return $this->pauseApprovals();
        }
        return false;
    }

}

