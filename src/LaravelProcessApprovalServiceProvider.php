<?php

namespace RingleSoft\LaravelProcessApproval;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use RingleSoft\LaravelProcessApproval\Console\Commands\FlowCommand;
use RingleSoft\LaravelProcessApproval\Console\Commands\StepCommand;
use RingleSoft\LaravelProcessApproval\View\Components\ApprovalActions;
use RingleSoft\LaravelProcessApproval\View\Components\ApprovalStatusSummary;

class LaravelProcessApprovalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(Facades\ProcessApproval::class, 'ProcessApproval');
    }

    public function boot(): void
    {
        Blade::component('approval-actions', ApprovalActions::class, 'ringlesoft');
        Blade::component('approval-status-summary', ApprovalStatusSummary::class, 'ringlesoft');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'ringlesoft');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->publishItems();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/process_approval.php', 'process_approval'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
//                InstallCommand::class,
                FlowCommand::class,
                StepCommand::class,
            ]);
        }
    }

    private function publishItems(): void
    {
        if (!function_exists('config_path') || !$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/process_approval.php' => config_path('process_approval.php'),
        ], 'approvals-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'approvals-migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ringlesoft/process_approval'),
        ], 'approvals-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/ringlesoft/process_approval'),
        ], 'approvals-translations');
    }
}
