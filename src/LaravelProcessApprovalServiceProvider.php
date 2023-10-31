<?php

namespace RingleSoft\LaravelProcessApproval;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use RingleSoft\LaravelProcessApproval\Console\Commands\FlowCommand;
use RingleSoft\LaravelProcessApproval\Console\Commands\StepCommand;
use RingleSoft\LaravelProcessApproval\View\Components\ApprovalActions;

class LaravelProcessApprovalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->alias(Facades\ProcessApproval::class, 'ProcessApproval');
    }

    public function boot()
    {
        Blade::component('approval-actions', ApprovalActions::class, 'ringlesoft');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ringlesoft');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/ringlesoft/process_approval'),
            __DIR__.'/../config/process_approval.php' => config_path('process_approval.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/process_approval.php', 'process_approval'
        );


        if ($this->app->runningInConsole()) {
            $this->commands([
//                InstallCommand::class,
                FlowCommand::class,
                StepCommand::class,
            ]);
        }
    }

}
