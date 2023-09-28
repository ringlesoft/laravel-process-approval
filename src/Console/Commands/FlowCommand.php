<?php

namespace RingleSoft\LaravelProcessApproval\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use Symfony\Component\Console\Question\ChoiceQuestion;
use function Laravel\Prompts\text;

class FlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process-approval:flow {action} {params?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Approval Flow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        switch($this->argument('action')){
            case 'add':
                $model = $this->argument('params')
                    ??
                    $model = $this->ask("Enter the name of the model you want to make approvable");
                $this->addFlow($model);
                break;
            case 'remove':
                $flowsArray = ProcessApprovalFlow::query()
                    ->get()
                    ->pluck('id', "id")
                    ->toArray();
                $choice = $this->choice('Choose an option:', $flowsArray);
                $this->removeFlow($choice);
                break;
            default:
                print('Unknown action '. $this->argument('action'));
        }
    }

    /**
     * Create a new approval flow
     * @param $name
     * @return true
     */
    private function addFlow($name) {

            if(!Str::contains($name, '\\')){
                $name = config('process_approval.models_path')."\\{$name}";
            }
            if(class_exists($name)){
                try {
                    if(ProcessApprovalFlow::query()->where('approvable_type', $name)->exists()){
                        $this->alert('This model already exists');
                    } else {
                        ProcessApprovalFlow::query()->create([
                            'name' => Str::of($name)->afterLast( '\\')->snake(' ')->title()->toString(),
                            'approvable_type' => get_class(new $name()),
                        ]);
                    }
                    $this->line("{$name} created successfully!");

                } catch (\Exception $e) {
                    echo "Failed to create Flow: " . $e->getMessage();
                }
            } else {
                echo "The model `{$name}` you specified doesn't exist";
            }

        return true;
    }

    /**
     * Remove approval flow
     * @param $name
     * @return void
     */
    public function removeFlow($flow)
    {
        $flow = ProcessApprovalFlow::find($flow);
        if($flow){
            if($flow->delete()) {
                $this->line("{$flow} removed successfully!");
            } else {
                $this->alert("Failed to remove {$flow}", 'critical');
            }
        } else {
            $this->alert("Flow doesn't exist on the approval flows table");
        }
    }
}
