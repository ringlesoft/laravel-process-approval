<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;

class ApprovalFlowExistsException extends Exception
{
    public static function create(string $name, string $flow): static
    {
        return new static("The flow `{$name}` can't be created. Another flow for the model `{$flow}` already exists.");
    }
}
