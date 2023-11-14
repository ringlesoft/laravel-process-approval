<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;

class NoFurtherApprovalStepsException extends \Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The record (`{$model->id}`) has already been approved. No further steps are available.");
    }
}
