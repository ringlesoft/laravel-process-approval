<?php

namespace RingleSoft\LaravelProcessApproval\Enums;

enum ApprovalActionEnum: string
{
    case CREATED = 'Created';
    case SUBMITTED = 'Submitted';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case RETURNED = 'Returned';
    case DISCARDED = 'Discarded';

}
