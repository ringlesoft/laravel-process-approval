<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\ProcessApproval;
use Workbench\App\Models\TestModel;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function testTrial()
    {
        $this->assertTrue(true);
    }

//    public function testCreateApprovalFlow(): void
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $this->assertInstanceOf(ProcessApprovalFlow::class, $flow);
//        $this->assertEquals('Test Flow', $flow->name);
//        $this->assertEquals(TestModel::class, $flow->approvable_type);
//    }

//    public function testCreateApprovalFlowStep()
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $step = $processApproval->createStep($flow->id, 1, 'approve');
//
//        $this->assertInstanceOf(ProcessApprovalFlowStep::class, $step);
//        $this->assertEquals($flow->id, $step->process_approval_flow_id);
//        $this->assertEquals(1, $step->role_id);
//        $this->assertEquals('approve', $step->approval_type);
//    }
//
//    public function testGetApprovalFlow()
//    {
//        $processApproval = new ProcessApproval();
//        $createdFlow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $retrievedFlow = ProcessApprovalFlow::where('approvable_type', TestModel::class)->first();
//
//        $this->assertInstanceOf(ProcessApprovalFlow::class, $retrievedFlow);
//        $this->assertEquals($createdFlow->id, $retrievedFlow->id);
//        $this->assertEquals('Test Flow', $retrievedFlow->name);
//        $this->assertEquals(TestModel::class, $retrievedFlow->approvable_type);
//    }
//
//    public function testGetApprovalFlowSteps()
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $step1 = $processApproval->createStep($flow->id, 1, 'approve');
//        $step2 = $processApproval->createStep($flow->id, 2, 'approve');
//
//        $steps = $flow->steps()->orderBy('order')->get();
//
//        $this->assertCount(2, $steps);
//        $this->assertEquals($step1->id, $steps[0]->id);
//        $this->assertEquals($step2->id, $steps[1]->id);
//    }
//
//    public function testCreateComplexApprovalFlow()
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Complex Flow', TestModel::class);
//
//        $step1 = $processApproval->createStep($flow->id, 1, 'approve');
//        $step2 = $processApproval->createStep($flow->id, 2, 'review');
//        $step3 = $processApproval->createStep($flow->id, 3, 'approve');
//
//        $retrievedFlow = ProcessApprovalFlow::with('steps')->find($flow->id);
//
//        $this->assertCount(3, $retrievedFlow->steps);
//        $this->assertEquals('approve', $retrievedFlow->steps[0]->approval_type);
//        $this->assertEquals('review', $retrievedFlow->steps[1]->approval_type);
//        $this->assertEquals('approve', $retrievedFlow->steps[2]->approval_type);
//    }
//
//    public function testUpdateApprovalFlow()
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $flow->name = 'Updated Flow';
//        $flow->save();
//
//        $updatedFlow = ProcessApprovalFlow::find($flow->id);
//
//        $this->assertEquals('Updated Flow', $updatedFlow->name);
//    }
//
//    public function testDeleteApprovalFlow()
//    {
//        $processApproval = new ProcessApproval();
//        $flow = $processApproval->createFlow('Test Flow', TestModel::class);
//
//        $flowId = $flow->id;
//        $flow->delete();
//
//        $this->assertNull(ProcessApprovalFlow::find($flowId));
//    }
}
