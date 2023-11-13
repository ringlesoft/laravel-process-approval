<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;

class ApprovalFlowModelDoesNotExistsException extends \Exception
{
    public static function create($modelName): static
    {
        return new static("The Model `{$modelName}` does not exist.");
    }
}
