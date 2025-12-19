<?php

namespace RingleSoft\LaravelProcessApproval\Traits;

use Illuminate\Support\Str;

trait UsesProcessApprovalUuids
{
    protected function initializeUsesProcessApprovalUuids(): void
    {
        if (!config('process_approval.use_uuids', false)) {
            return;
        }

        $this->incrementing = false;
        $this->keyType = 'string';
    }

    protected static function bootUsesProcessApprovalUuids(): void
    {
        static::creating(static function ($model) {
            if (!config('process_approval.use_uuids', false)) {
                return;
            }
            $keyName = $model->getKeyName();
            if (empty($model->{$keyName})) {
                $model->{$keyName} = (string)Str::uuid7();
            }
        });
    }
}
