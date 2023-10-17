<?php

namespace RingleSoft\LaravelProcessApproval\Console\Commands;

use Illuminate\Console\Command;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\Role;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class StepCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-approval:step {action} {params?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Approval Flow Step';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $flows = ProcessApprovalFlow::query()->get();
        if(!$flows->count()){
            alert('There are no flows defined');
            return;
        }
        $flowsArray = $flows->pluck('name', 'id')->toArray();
        switch ($this->argument('action')) {
            case 'add':
                $model = $this->argument('params')
                    ??
                    $model = select("Select the Model to which you want to add steps:", $flowsArray);
                $this->addStep($model);
                break;
            case 'remove':
                $steps = [];
                $flows = ProcessApprovalFlow::query()->with(['steps', 'steps.role'])->whereHas('steps')->get();
                foreach ($flows as $index => $flow) {
                    foreach ($flow->steps as $index2 => $step) {
                        $steps[$step->id] = $flow->name . ' '. $step->role->name . " - ". $step->action;
                    }
                }
                if(!count($steps)){
                   info('No steps available!');
                   return;
                }
                $step = select("Which step do you want to remove?", $steps);
                $this->removeStep($step);
                break;
            default:
                print('Unknown action ' . $this->argument('action'));
        }
    }

    /**
     * Create a new step
     * @param $flowId
     * @return true
     */
    private function addStep($flowId)
    {
        $flow = ProcessApprovalFlow::query()->find($flowId);
        $rolesModel = config('process_approval.roles_model');
        if(!class_exists($rolesModel)){
           alert("`roles_model` not configured");
        }
        $roleChoices = ($rolesModel)::query()->get()->pluck('name', 'id')->toArray();
        $role = select(
            'Select the role to be that will approve this model',
            $roleChoices,
        );
        $action = select("Select the type of action", ['Approve', 'Check'], 'Approve');
        $data = [
            'role_id' => $role,
            'action' => $action,
            'active' => 1
        ];
        if($flow->steps()->create($data)){
            info('Step created Successfully');
        } else {
            alert('Failed to create step');
        }

        return true;
    }

    /**
     * Remove a step
     * @param $stepId
     * @return void
     */
    public function removeStep($stepId)
    {

        $step = ProcessApprovalFlowStep::query()->find($stepId);
        if ($step) {
            if ($step->delete()) {
                info("Step removed successfully!");
            } else {
                alert("Failed to remove step");
            }
        } else {
            alert("Step doesn't exist on the approval flow steps table");
        }
    }
}
