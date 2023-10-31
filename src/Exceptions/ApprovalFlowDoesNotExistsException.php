<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;

class ApprovalFlowDoesNotExistsException extends \Exception
{
    public static function create(): static
    {
        return new static("The flow specified doesn't exist.");
    }
}
