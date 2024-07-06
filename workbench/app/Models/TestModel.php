<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Traits\Approvable;

class TestModel extends Model implements \RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel
{
    use  Approvable;
    protected $guarded = [];

    public static function getApprovableType(): string
    {
        return 'TestModel';
    }

    public function pauseApprovals() {
        return false;
    }


    public function onApprovalCompleted(ProcessApproval $approval): bool
    {
        return true;
    }
}
