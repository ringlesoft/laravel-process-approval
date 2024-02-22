<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;

class ApprovalFlowStepDoesNotExistsException extends Exception
{
    public static function create(): static
    {
        return new static("The Step specified doesn't exist.");
    }
}
