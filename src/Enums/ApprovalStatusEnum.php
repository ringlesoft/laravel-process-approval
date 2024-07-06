<?php

namespace RingleSoft\LaravelProcessApproval\Enums;


enum ApprovalStatusEnum: string
{
    case CREATED = 'Created';
    case SUBMITTED = 'Submitted';
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case DISCARDED = 'Discarded';
    case RETURNED = 'Returned';
    case OVERRIDDEN = 'Overridden';
}
