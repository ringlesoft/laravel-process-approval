<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Traits\Approvable;
/**
 * @mixin Builder
 */
class TestModel extends Model implements ApprovableModel
{
    use  Approvable;
    protected $guarded = [];

    public bool $autoSubmit = false;


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

    public static function readyForSubmit(): static
    {

        $testModel = self::createSample();
        $testModel->refresh();
        return $testModel;
    }

    public static function readyForApproval(): static
    {

        $testModel = self::createSample();
        $testModel->submit();
        $testModel->refresh();
        return $testModel;
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
        return self::create([ 'description' => 'This is a test model', 'amount' => rand(100, 1000), 'status' => 'pending']);
    }
}
