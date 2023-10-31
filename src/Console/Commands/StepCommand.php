<?php

namespace RingleSoft\LaravelProcessApproval\Console\Commands;

use Illuminate\Console\Command;
use RingleSoft\LaravelProcessApproval\Facades\ProcessApproval;
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
        $flows = ProcessApproval::flows();
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
                $flows = ProcessApproval::flowsWithSteps();
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
        try {
            ProcessApproval::createStep(flowId: $flowId, roleId: $role, action: $action );
            info('Step created Successfully');
        } catch (\Exception $e) {
            alert('Failed to create step. '. $e->getMessage());
            return false;
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
        try {
            ProcessApproval::deleteStep($stepId);
            info("Step removed successfully!");
        } catch (\Exception $e) {
            alert("Failed to remove step. ". $e->getMessage());
        }

    }
}
