<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

trait LoadApprovableTrait
{
    /**
     * Preload real steps for a collection of items
     *
     * @param Collection|Model[] $items
     * @return Collection|Model[]
     */
    public static function loadApprovalStepsFor($items)
    {
        $stepIds = $items
            ->pluck('approvalStatus.steps')
            ->flatten(1)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $realSteps = ProcessApprovalFlowStep::with('role')
            ->whereIn('id', $stepIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $ids = collect($item->approvalStatus?->steps)->pluck('id')->filter();

            $item->steps = $item->approvalStatus?->steps ?? [];

            $item->real_steps = $ids
                ->map(fn($id) => $realSteps->get($id))
                ->filter()
                ->values();
        }

        return $items;
    }
}
