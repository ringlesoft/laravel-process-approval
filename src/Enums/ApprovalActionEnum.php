<?php

namespace RingleSoft\LaravelProcessApproval\Enums;

enum ApprovalActionEnum: string
{
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case DISCARDED = 'Discarded';
}
