<?php

use Illuminate\Foundation\Application;
use Orchestra\Testbench\Concerns\WithWorkbench;
use RingleSoft\LaravelProcessApproval\LaravelProcessApprovalServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;



    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
        $this->artisan('migrate:fresh', ['--database' => 'testbench'])->run();
    }


    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Load the custom .env file
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, '.env.testing');
        $dotenv->load();
    }


    /**
     * @param Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [LaravelProcessApprovalServiceProvider::class];
    }


}
