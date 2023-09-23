<?php

namespace RingleSoft\LaravelProcessApproval\Enums;

enum RequestStatusEnum: string
{
    case PENDING = 'Pending';
    case SUBMITTED = 'Submitted';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case DISCARDED = 'Discarded';
}
