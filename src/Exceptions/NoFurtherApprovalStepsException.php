<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class NoFurtherApprovalStepsException extends Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The record (`{$model->id}`) has already been approved. No further steps are available.");
    }
}
