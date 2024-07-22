<?php

namespace RingleSoft\LaravelProcessApproval\Enums;

enum ApprovalTypeEnum: string
{
    case APPROVE = 'APPROVE';
    case CHECK = 'CHECK';

    case VERIFY = 'VERIFY';
}
