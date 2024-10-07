<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ApprovalCompletedCallbackFailedException extends Exception
{
    public static function create(ApprovableModel $model, $message = null): static
    {
        return new static($message ?? "The approval completed callback method returned false. Make sure it is defined and returns true.");
    }
}
