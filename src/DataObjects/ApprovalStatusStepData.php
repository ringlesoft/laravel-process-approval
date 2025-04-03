<?php

namespace RingleSoft\LaravelProcessApproval\DataObjects;

use Illuminate\Support\Collection;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalStatus;

class ApprovalStatusStepData
{

    public function __construct(
        private readonly int              $id,
        private readonly ApprovalTypeEnum $action,
        private readonly int|string       $roleId,
        private readonly string|null      $roleName = null,
        private int|null                  $processApprovalId = null,
        private ApprovalActionEnum|null   $processApprovalAction = null,
        private readonly bool|null $active = null
    )
    {
    }



    public function updateApproval(ProcessApproval $approval): static
    {
        $this->processApprovalId = $approval->id;
        if(is_string($approval->approval_action)) {
            $this->processApprovalAction = ApprovalActionEnum::from($approval->approval_action);
        } else if ($approval->approval_action instanceof ApprovalActionEnum) {
            $this->processApprovalAction = $approval->approval_action;
        }
        return $this;
    }

    /**
     * Mark the approval status step as returned
     * @return $this
     */
    public function makeReturned(): static
    {
        ProcessApproval::query()
            ->where('process_approval_flow_step_id', $this->id)
            ->update(['approval_action' => ApprovalStatusEnum::OVERRIDDEN->value]);
        $this->processApprovalAction = ApprovalActionEnum::RETURNED;
        return $this;
    }

    /**
     * @return $this
     */
    public function reset(): static
    {
        $this->processApprovalId = null;
        $this->processApprovalAction = null;
        return $this;
    }


    public function isDone(): bool
    {
        return $this->processApprovalAction !== ApprovalActionEnum::RETURNED && $this->processApprovalId !== null;
    }
    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->processApprovalAction === ApprovalActionEnum::APPROVED;
    }

    public function isReturned(): bool
    {
        return $this->processApprovalAction === ApprovalActionEnum::RETURNED;
    }

    public function isDiscarded(): bool
    {
        return $this->processApprovalAction === ApprovalActionEnum::DISCARDED;
    }

    /**
     * @param ProcessApprovalFlowStep $step
     * @return static
     */
    public static function fromApprovalFlowStep(ProcessApprovalFlowStep $step): static
    {
        return new static(
            id: $step->id,
            action: ApprovalTypeEnum::from($step->action) ?? ApprovalTypeEnum::APPROVE,
            roleId: $step->role_id,
            roleName: $step->role?->name ?? null,
            processApprovalId: null,
            processApprovalAction:  null
        );
    }

    /**
     * @param ApprovableModel $approvable
     * @return Collection
     */
    public static function collectionFromApprovable(ApprovableModel $approvable): Collection
    {
        return collect($approvable->approvalStatus->steps ?? [])->map(static function ($step) {
            return static::fromArray($step);
        });
    }

    /**
     * @param ProcessApprovalStatus $status
     * @return Collection
     */
    public static function collectionFromProcessApprovalStatus(ProcessApprovalStatus $status): Collection
    {
        return collect($status->steps ?? [])->map(static function ($step) {
            return static::fromArray($step);
        });
    }

    /**
     * @param array $steps
     * @return Collection
     */
    public static function collectionFromArray(array $steps): Collection
    {
      return collect($steps)->map(static function ($step) {
          return static::fromArray($step);
      });
    }

    /**
     * @param Collection $steps
     * @return array
     */
    public static function collectionToArray(Collection $steps): array
    {
        return $steps->map(static function ($step) {
            return $step->toArray();
        })->toArray();
    }

    /**
     * Check if the step belongs to the specified step
     * @param int|ProcessApprovalFlowStep $step
     * @return bool
     */
    public function belongsToStep(int|ProcessApprovalFlowStep $step): bool
    {
        if($step instanceof ProcessApprovalFlowStep) {
            $step = $step->id;
        }
        return $this->id === $step;
    }


    /**
     * Check if the step belongs to the specified approval
     * @param int|ProcessApproval $approval
     * @return bool
     */
    public function belongsToApproval(int|ProcessApproval $approval): bool
    {
        if($approval instanceof ProcessApproval) {
            $approval = $approval->id;
        }
        return $this->processApprovalId === $approval;
    }

    /**
     * @param array $data
     * @return $this
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            action: ApprovalTypeEnum::from($data['action']) ?? ApprovalTypeEnum::APPROVE,
            roleId: $data['role_id'],
            roleName: $data['role_name'] ?? null,
            processApprovalId: $data['process_approval_id'] ?? null,
            processApprovalAction: !empty($data['process_approval_action']) ? ApprovalActionEnum::from($data['process_approval_action']) : null,
            active: $data['active'] ?? null
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action->value,
            'process_approval_id' => $this->processApprovalId,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'process_approval_action' => $this->processApprovalAction,
            'active' => $this->active
        ];
    }
}
