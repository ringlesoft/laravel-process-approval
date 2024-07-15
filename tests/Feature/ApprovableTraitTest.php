<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestAlreadySubmittedException;
use RingleSoft\LaravelProcessApproval\Exceptions\RequestNotSubmittedException;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;
use Workbench\App\Models\TestModel;
use Workbench\App\Models\User;

class ApprovableTraitTest extends TestCase
{
    use RefreshDatabase;

    public TestModel $testModel;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        TestModel::seedSteps();
        $this->login();
    }


    public function testSubmitRequest(): void
    {

        $testModel = TestModel::createSample();
        $testModel->submit();
        $testModel->refresh();

        $this->assertTrue($testModel->isSubmitted());
        $this->assertEquals(ApprovalStatusEnum::SUBMITTED->value, $testModel->approvalStatus?->status);
    }

    public function testSubmitAlreadySubmittedRequest(): void
    {
        $testModel = TestModel::createSample();
        $testModel->submit();
        $testModel->refresh();
        $this->expectException(RequestAlreadySubmittedException::class);
        $testModel->submit();
    }

    public function testApproveRequest(): void
    {
        $testModel = TestModel::readyForApproval();
        $comment = 'I Approve this';
        $approval = $testModel->approve($comment);
        $testModel->refresh();
        $this->assertInstanceOf(ProcessApproval::class, $approval);
        $this->assertValidProcessApprovalCreated($testModel, $testModel->approvalFlowSteps()->first(), $comment, $approval);
        $this->assertTrue($testModel->isApprovalStarted());
    }

    public function testRejectRequest(): void
    {
        $testModel = TestModel::readyForApproval();
        $comment = 'I Reject this';
        $approval = $testModel->reject($comment);
        $testModel->refresh();
        $this->assertInstanceOf(ProcessApproval::class, $approval);
        $this->assertValidProcessApprovalCreated($testModel, $testModel->approvalFlowSteps()->first(), $comment, $approval, ApprovalActionEnum::REJECTED);
        $this->assertTrue($testModel->isRejected());
    }


    public function testReturnRequest(): void
    {
        $testModel = TestModel::readyForApproval();
        $comment = 'I Return this';
        $approval = $testModel->return($comment);
        $this->assertInstanceOf(ProcessApproval::class, $approval);
        $this->assertValidProcessApprovalCreated($testModel, $testModel->approvalFlowSteps()->first(), $comment, $approval, ApprovalActionEnum::RETURNED);
        $testModel->refresh();
        $this->assertEquals(ApprovalStatusEnum::CREATED->value, $testModel->approvalStatus->status);
        $this->assertEquals($testModel->nextApprovalStep()->id, $testModel->approvalStatus->steps[0]['id']);
    }


    public function testReturnRequestToPreviousStep(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('I Approve this as first person');
        $testModel->refresh();
        $comment = 'I Return this';
        $approval = $testModel->return($comment);
        $this->assertInstanceOf(ProcessApproval::class, $approval);
        $testModel->refresh();
        $this->assertEquals(ApprovalStatusEnum::RETURNED->value, $testModel->approvalStatus->status);
        $this->assertEquals($testModel->nextApprovalStep()->id, $testModel->approvalStatus->steps[0]['id']);
        $this->assertTrue($testModel->isReturned());
    }

    public function testDiscardRequest(): void
    {

        $testModel = TestModel::readyForApproval();
        $testModel->reject('I Reject this');
        $testModel->refresh();
        $approval = $testModel->discard($comment = 'I Discard this');
        $testModel->refresh();
        $this->assertValidProcessApprovalCreated($testModel, $testModel->approvalFlowSteps()->first(), $comment, $approval, ApprovalActionEnum::DISCARDED);
        $this->assertEquals(ApprovalStatusEnum::DISCARDED->value, $testModel->approvalStatus->status);
        $this->assertTrue($testModel->isDiscarded());

    }

    public function testUndoLastApproval(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('First approval');
        $testModel->refresh();
        $testModel->undoLastApproval();
        $testModel->refresh();

        $this->assertFalse($testModel->isApprovalStarted());
        $this->assertEquals(ApprovalStatusEnum::PENDING->value, $testModel->approvalStatus->status);
    }


    public function testGetNextApprovers(): void
    {
        $testModel = TestModel::readyForApproval();
        $nextApprovers = $testModel->getNextApprovers();
        $this->assertNotEmpty($nextApprovers);
        $this->assertInstanceOf(User::class, $nextApprovers->first());
    }

    public function testCanBeApprovedBy()
    {
        $testModel = TestModel::readyForApproval();
        $canBeApproved = $testModel->canBeApprovedBy(User::find(1));
        $this->assertTrue($canBeApproved);
    }

    public function testIsApprovalCompleted(): void
    {
        $testModel = TestModel::readyForApproval();
        $approvalSteps = $testModel->approvalFlowSteps();
        foreach ($approvalSteps as $step) {
            $testModel->approve('Approval for step: ' . $step->id);
            $testModel->refresh();
        }
        $this->assertTrue($testModel->isApprovalCompleted());
    }





    public function testIsSubmitted()
    {
        $testModel = TestModel::createSample();
        $this->assertFalse($testModel->isSubmitted());
        $testModel->submit();
        $testModel->refresh();
        $this->assertTrue($testModel->isSubmitted());
    }

    public function testIsRejected(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->reject('Rejected');
        $testModel->refresh();
        $this->assertTrue($testModel->isRejected());
    }

    public function testIsDiscarded(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->reject('Rejected');
        $testModel->refresh();
        $testModel->discard('Discarding');
        $testModel->refresh();
        $this->assertTrue($testModel->isDiscarded());
    }

    public function testIsReturned(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('First approval');
        $testModel->refresh();
        $testModel->return('Returned');
        $testModel->refresh();
        $this->assertTrue($testModel->isReturned());
    }

    public function testIsApprovalStarted(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('First approval');
        $testModel->refresh();
        $this->assertTrue($testModel->isApprovalStarted());
    }

    public function testNextApprovalStep(): void
    {
        $testModel = TestModel::readyForApproval();
        $nextStep = $testModel->nextApprovalStep();
        $this->assertInstanceOf(ProcessApprovalFlowStep::class, $nextStep);
        $this->assertEquals($testModel->approvalFlowSteps()->first()->id, $nextStep->id);
    }

    public function testPreviousApprovalStep(): void
    {
        $testModel = TestModel::readyForApproval();
        $approval = $testModel->approve('First approval');
        $testModel->refresh();
        $previousStep = $testModel->previousApprovalStep();

        $this->assertInstanceOf(ProcessApprovalFlowStep::class, $previousStep);
        $this->assertEquals($approval->processApprovalFlowStep?->id, $previousStep->id);
    }

    public function testMakeApprovable(): void
    {
        ProcessApprovalFlow::query()->delete();
        $result = TestModel::makeApprovable([3 => ApprovalTypeEnum::APPROVE->value, 4 => ApprovalTypeEnum::CHECK->value]);

        $this->assertTrue($result);
        $flow = ProcessApprovalFlow::where('approvable_type', TestModel::class)->first();
        $this->assertNotNull($flow);
        $this->assertEquals(2, $flow->steps()->count());
    }

    public function testApproveNonSubmittedRequest(): void
    {
        $testModel = TestModel::createSample();
        $this->expectException(RequestNotSubmittedException::class);
        $testModel->approve('First approval');
    }

    public function testRejectNonSubmittedRequest(): void
    {
        $testModel = TestModel::createSample();
        $this->expectException(RequestNotSubmittedException::class);
        $testModel->reject('Rejected');
    }

    public function testDiscardNonSubmittedRequest(): void
    {
        $testModel = TestModel::createSample();
        $this->expectException(RequestNotSubmittedException::class);
        $testModel->discard('Discarded');
    }

    public function testReturnNonSubmittedRequest(): void
    {
        $testModel = TestModel::createSample();
        $this->expectException(RequestNotSubmittedException::class);
        $testModel->return('Returned');
    }

    /**
     * @param TestModel $testModel
     * @param mixed $step
     * @param string $comment
     * @param ProcessApproval|Model $approval
     * @param ApprovalActionEnum $action
     * @return void
     */
    private function assertValidProcessApprovalCreated(TestModel $testModel, mixed $step, string $comment, ProcessApproval|Model $approval, ApprovalActionEnum $action = ApprovalActionEnum::APPROVED): void
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
