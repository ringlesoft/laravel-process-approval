<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class RequestAlreadySubmittedException extends Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The record with id `{$model->id}` has already been submitted.");
    }
}
