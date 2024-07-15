<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovalCompletedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessApprovedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessDiscardedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessRejectedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessReturnedEvent;
use RingleSoft\LaravelProcessApproval\Events\ProcessSubmittedEvent;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;
use Workbench\App\Models\TestModel;

class EventDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TestModel::seedSteps();
        $this->login();
    }


    public function testProcessSubmittedEventDispatched(): void
    {
        $testModel = TestModel::readyForSubmit();
        Event::fake();
        $testModel->submit();
        Event::assertDispatched(ProcessSubmittedEvent::class);
    }

    public function testProcessApprovedEventDispatched(): void
    {
        $testModel = TestModel::readyForApproval();
        Event::fake();
        $testModel->approve('I Approve this');
        Event::assertDispatched(ProcessApprovedEvent::class);
    }

    public function testProcessRejectedEventDispatched(): void
    {
            $testModel = TestModel::readyForApproval();
            Event::fake();
            $testModel->reject('I Reject this');
            Event::assertDispatched(ProcessRejectedEvent::class);
    }

    public function testProcessDiscardedEventDispatched(): void
    {
            $testModel = TestModel::readyForApproval();
            $testModel->reject("I dont want it");
            $testModel->refresh();
            Event::fake();
            $testModel->discard('I Discard this');
            Event::assertDispatched(ProcessDiscardedEvent::class);
    }

    public function testProcessReturnedEventDispatched(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('I Approve this');
        $testModel->refresh();
        Event::fake();
        $testModel->return('I Return this');
        Event::assertDispatched(ProcessReturnedEvent::class);
    }

    public function testProcessApprovalCompletedEventDispatched(): void
    {
        $testModel = TestModel::readyForApproval();
        $testModel->approve('First approval');
        $testModel->refresh();
        $testModel->approve('Second approval');
        $testModel->refresh();
        Event::fake();
        $testModel->approve('Third approval');
        $testModel->refresh();
        Event::assertDispatched(ProcessApprovalCompletedEvent::class);
    }
}
