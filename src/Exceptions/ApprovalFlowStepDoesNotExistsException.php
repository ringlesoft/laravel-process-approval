<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;

class ApprovalFlowStepDoesNotExistsException extends \Exception
{
    public static function create(): static
    {
        return new static("The Step specified doesn't exist.");
    }
}
