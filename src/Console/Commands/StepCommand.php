<?php

namespace RingleSoft\LaravelProcessApproval\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\Role;
use function Laravel\Prompts\text;

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
            $this->alert('There are no flows defined');
            return;
        }
        $flowsArray = $flows->pluck('name', 'id')->toArray();
        switch ($this->argument('action')) {
            case 'add':
                $model = $this->argument('params')
                    ??
                    $model = $this->choice("Select the Model to which you want to add steps:", $flowsArray);
                $this->addStep($model);
                break;
            case 'remove':
                $stepsArray = ProcessApprovalFlowStep::query()->with('role')->orderBy('order')->orderBy('id')->get()
                    ->only('id')
                    ->map(static function($item, $index){ $item->name = "Step " . $index + 1; return $item;})
                    ->toArray();
                if(!count($stepsArray)){
                   $this->info('No steps available!');
                   return;
                }
                $step = $this->choice("Which step do you want to remove?", $stepsArray);
                $this->removeStep($step);
                break;
            default:
                print('Unknown action ' . $this->argument('action'));
        }
    }

    /**
     * Create a new step
     * @param $name
     * @return true
     */
    private function addStep($name)
    {
        $flow = ProcessApprovalFlow::query()->where('name', $name)->first();
        $rolesModel = config('process_approval.roles_model');
        if(!class_exists($rolesModel)){
           $this->alert("`roles_model` not configured");
        }
        $roleChoices = ($rolesModel)::query()->get()->pluck('name', 'id')->toArray();
        $role = $this->choice(
            'Select the role to be that will approve this model',
            $roleChoices,
        );
        $action = $this->choice(
            "Select the type of action",
            [1 => 'Approve', 2 => 'Check'],
            'Approve'
        );
        $data = [
            'role_id' => array_flip($roleChoices)[$role],
            'action' => $action,
            'active' => 1
        ];
        if($flow->steps()->create($data)){
            $this->line('Step created Successfully');
        } else {
            $this->alert('Failed to create step', 'critical');
        }

        return true;
    }

    /**
     * Remove a step
     * @param $name
     * @return void
     */
    public function removeStep($name)
    {
        if (!Str::contains($name, '\\')) {
            $name = "\App\\Models\\{$name}";
        }
        $flow = ProcessApprovalFlowStep::query()->where('approvable_type', $name)->first();
        if ($flow) {
            if ($flow->delete()) {
                $this->line("{$name} removed successfully!");
            } else {
                $this->alert("Failed to remove {$name}", 'critical');
            }
        } else {
            $this->alert("{$name} doesn't exist on the approval flows table");
        }
    }
}
