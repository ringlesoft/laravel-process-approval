<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Traits\Approvable;

class TestModel extends Model implements ApprovableModel
{
    use  Approvable;
    protected $guarded = [];


    public static function seedSteps(): void
    {
        self::makeApprovable([
                [
                    'role_id' => 1,
                    'action' => ApprovalTypeEnum::CHECK->value
                ],
                [
                    'role_id' => 2,
                    'action' => ApprovalTypeEnum::CHECK->value
                ],
                [
                    'role_id' => 3,
                    'action' => ApprovalTypeEnum::APPROVE->value
                ]
            ]
        );
    }

    public function pauseApprovals() {
        return false;
    }


    public function onApprovalCompleted(ProcessApproval $approval): bool
    {
        return true;
    }

    public static function createSample(): static
    {
        return self::create([ 'description' => 'This is a test model', 'amount' => 100, 'status' => 'pending']);
    }
}
