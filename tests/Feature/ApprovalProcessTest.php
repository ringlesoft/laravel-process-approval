<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Feature;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalCompletedCallbackFailedException;
use RingleSoft\LaravelProcessApproval\Exceptions\ApprovalsPausedException;
use RingleSoft\LaravelProcessApproval\Exceptions\NoFurtherApprovalStepsException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestAlreadySubmittedException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestNotSubmittedException;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;
use Throwable;
use Workbench\App\Models\TestModel;
use Workbench\App\Models\User;
use Workbench\Database\Seeders\DatabaseSeeder;

class ApprovalProcessTest extends TestCase
{
    use RefreshDatabase;


    public function testCreateProcessApproval(): void
    {
        TestModel::seedSteps();
        $comment = 'This is OK';
        $this->login();
        $testModel = TestModel::readyForApproval();
        $approvalFlowSteps = TestModel::approvalFlow()->steps;
        $step = $approvalFlowSteps->first();
        $approval = $testModel->approve($comment);
        $this->assertValidProcessApprovalCreated($testModel, $step, $comment, $approval);
    }



    public function testProcessApprovalRelations(): void
    {
        $this->login();
        TestModel::seedSteps();
        $testModel = TestModel::readyForApproval();
        $approval = $testModel->approve("I Approve this");
        $approval->refresh();
        $this->assertInstanceOf(TestModel::class, $approval->approvable);
        $this->assertInstanceOf(ProcessApprovalFlowStep::class, $approval->processApprovalFlowStep);
    }

    public function testProcessApprovalScope(): void
    {
        $this->login();
        TestModel::seedSteps();
        $testModel = TestModel::readyForApproval();
        $testModel->approve("I Approve this");
        $testModel->reject("I Reject this");
        $approvedCount = ProcessApproval::where('approval_action', ApprovalActionEnum::APPROVED->value)->count();
        $rejectedCount = ProcessApproval::where('approval_action', ApprovalActionEnum::REJECTED->value)->count();
        $this->assertEquals(1, $approvedCount);
        $this->assertEquals(1, $rejectedCount);
    }


    /**
     * @param TestModel $testModel
     * @param mixed $step
     * @param string $comment
     * @param ProcessApproval|Model $approval
     * @param ApprovalActionEnum $action
     * @return void
     */
    public function assertValidProcessApprovalCreated(TestModel $testModel, mixed $step, string $comment, ProcessApproval|Model $approval, ApprovalActionEnum $action = ApprovalActionEnum::APPROVED): void
    {
        $user = Auth::user();
        $expected = [
            'approvable_type' => TestModel::getApprovableType(),
            'approvable_id' => $testModel->id,
            'process_approval_flow_step_id' => $step->id,
            'approval_action' => $action,
            'comment' => $comment,
            'user_id' => $user->id,
            'approver_name' => $user->name,
        ];

        $this->assertInstanceOf(ProcessApproval::class, $approval);
        $this->assertEquals($testModel->id, $approval->approvable_id);
        $this->assertEquals($step->id, $approval->process_approval_flow_step_id);
        $this->assertEquals($action, $approval->approval_action);
        $returned = $approval->toArray();
        unset($returned['created_at'], $returned['updated_at'], $returned['id']);
        $this->assertEquals($expected, $returned);
    }

}
