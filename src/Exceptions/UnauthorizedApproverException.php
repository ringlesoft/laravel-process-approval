<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class UnauthorizedApproverException extends Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("The user does not have the role required to act on the current approval step");
    }
}
