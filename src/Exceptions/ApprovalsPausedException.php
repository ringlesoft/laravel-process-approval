<?php

namespace RingleSoft\LaravelProcessApproval\Exceptions;

use Exception;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ApprovalsPausedException extends Exception
{
    public static function create(ApprovableModel $model): static
    {
        return new static("Approvals has been paused for this record");
    }
}
