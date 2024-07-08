<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalStatus;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;
use Workbench\App\Models\TestModel;
use Workbench\Database\Seeders\DatabaseSeeder;

class ApprovalStatusTest extends TestCase
{
    use RefreshDatabase;



    public function testCreatesApprovalStatusAutomatically(): void
    {
        $testModel = TestModel::createSample();
        $registeredSteps = collect($testModel->approvalStatus->steps ?? []);
        $status = $testModel->approvalStatus;

        $this->assertInstanceOf(ProcessApprovalStatus::class, $status);
        $this->assertEquals(TestModel::class, $status->approvable_type);
        $this->assertEquals($testModel->id, $status->approvable_id);
        $this->assertEquals(ApprovalStatusEnum::CREATED->value, $status->status);
    }

    public function testUpdateApprovalStatus()
    {
        $testModel = TestModel::createSample();

        $status = $testModel->approvalStatus;

        $status->status = ApprovalStatusEnum::SUBMITTED->value;
        $status->save();

        $updatedStatus = ProcessApprovalStatus::find($status->id);
        $this->assertEquals(ApprovalStatusEnum::SUBMITTED->value, $updatedStatus->status);
    }

    public function testGetApprovalStatus()
    {
        $testModel = TestModel::createSample();
        $retrievedStatus = $testModel->approvalStatus;

        $this->assertInstanceOf(ProcessApprovalStatus::class, $retrievedStatus);
        $this->assertEquals(ApprovalStatusEnum::CREATED->value, $retrievedStatus->status);
    }

    public function testCreatesApprovalStatusStepsProperly(): void
    {
        TestModel::seedSteps();
        $testModel = TestModel::createSample();
        $availableSteps = $testModel->approvalFlowSteps();

        $statusSteps = $testModel->approvalStatus->steps;


        $this->assertCount($availableSteps->count(), $statusSteps);
        foreach ($statusSteps as $statusStep) {
//            $this->assertTrue($availableSteps->contains('id', $statusStep['id']));
        }
    }
}
