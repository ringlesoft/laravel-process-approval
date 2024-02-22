<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;

class ApprovalFlowDoesNotExistsException extends Exception
{
    public static function create(): static
    {
        return new static("The flow specified doesn't exist.");
    }
}
