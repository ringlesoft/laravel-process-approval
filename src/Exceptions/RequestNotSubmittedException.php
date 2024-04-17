<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class RequestNotSubmittedException extends Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The record  `{$model->id}` is not submitted yet.");
    }
}
