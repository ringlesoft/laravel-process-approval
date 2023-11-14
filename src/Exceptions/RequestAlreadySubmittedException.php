<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;

class RequestAlreadySubmittedException extends \Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The record with id `{$model->id}` has already been submitted.");
    }
}
