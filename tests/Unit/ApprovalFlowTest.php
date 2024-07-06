<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;
use Workbench\App\Models\TestModel;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateApprovalFlow(): void
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
        $this->assertEquals('Test Flow', $flow->name);
        $this->assertEquals(TestModel::class, $flow->approvable_type);
    }

    public function testCanCreateApprovalFlowStep(): void
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
        $step = $processApproval->createStep($flow->id, 1, ApprovalTypeEnum::APPROVE->value);

        $this->assertEquals($flow->id, $step->process_approval_flow_id);
        $this->assertEquals(1, $step->role_id);
        $this->assertEquals(ApprovalTypeEnum::APPROVE->value, $step->action);
    }

    public function testCanGetApprovalFlowOfModel(): void
    {
        $processApproval = new ProcessApproval();
        $createdFlow = $processApproval->createFlow('Test Flow', TestModel::class);

        $retrievedFlow = ProcessApprovalFlow::where('approvable_type', TestModel::class)->first();

        $this->assertInstanceOf(ProcessApprovalFlow::class, $retrievedFlow);
        $this->assertEquals($createdFlow->id, $retrievedFlow->id);
        $this->assertEquals('Test Flow', $retrievedFlow->name);
        $this->assertEquals(TestModel::class, $retrievedFlow->approvable_type);
    }

    public function testCanGetApprovalFlowSteps()
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Test Flow', TestModel::class);

        $step1 = $processApproval->createStep($flow->id, 1, ApprovalTypeEnum::CHECK->value);
        $step2 = $processApproval->createStep($flow->id, 2, ApprovalTypeEnum::APPROVE->value);

        $steps = $flow->steps()->orderBy('order')->get();

        $this->assertCount(2, $steps);
        $this->assertEquals($step1->id, $steps[0]->id);
        $this->assertEquals($step2->id, $steps[1]->id);
    }

    public function testCanCreateComplexApprovalFlow()
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Complex Flow', TestModel::class);

        $step1 = $processApproval->createStep($flow->id, 1, ApprovalTypeEnum::APPROVE->value);
        $step2 = $processApproval->createStep($flow->id, 2, ApprovalTypeEnum::CHECK->value);
        $step3 = $processApproval->createStep($flow->id, 3, ApprovalTypeEnum::APPROVE->value);

        $retrievedFlow = ProcessApprovalFlow::with('steps')->find($flow->id);
        $this->assertCount(3, $retrievedFlow->steps);
        $this->assertEquals(ApprovalTypeEnum::APPROVE->value, $retrievedFlow->steps[0]->action);
        $this->assertEquals(ApprovalTypeEnum::CHECK->value, $retrievedFlow->steps[1]->action);
        $this->assertEquals(ApprovalTypeEnum::APPROVE->value, $retrievedFlow->steps[2]->action);
    }

    public function testCanUpdateApprovalFlow()
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Test Flow', TestModel::class);

        $flow->name = 'Updated Flow';
        $flow->save();
        $updatedFlow = ProcessApprovalFlow::find($flow->id);
        $this->assertEquals('Updated Flow', $updatedFlow->name);
    }

    public function testCanDeleteApprovalFlow(): void
    {
        $processApproval = new ProcessApproval();
        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
        $processApproval->createStep($flow->id, 1, ApprovalTypeEnum::APPROVE->value);
        $processApproval->createStep($flow->id, 2, ApprovalTypeEnum::CHECK->value);
        $processApproval->createStep($flow->id, 3, ApprovalTypeEnum::APPROVE->value);

        $flowId = $flow->id;
        $flow->delete();

        $this->assertNull(ProcessApprovalFlow::find($flowId));
        $this->assertEquals(0, ProcessApprovalFlowStep::where('process_approval_flow_id', $flowId)->count());
    }
}
