<?php

namespace RingleSoft\LaravelProcessApproval\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Concerns\WithWorkbench;
use RingleSoft\LaravelProcessApproval\LaravelProcessApprovalServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Workbench\Database\Seeders\DatabaseSeeder;
use function Orchestra\Testbench\workbench_path;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

#[WithMigration]
class TestCase extends OrchestraTestCase
{
    use WithWorkbench;
    use InteractsWithViews;
//    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
//        $this->seed(DatabaseSeeder::class);


    }


    /**
     * @param Application $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [LaravelProcessApprovalServiceProvider::class];
    }


    protected function getPackageAliases($app): array
    {
        return [
            'ProcessApproval' => \RingleSoft\LaravelProcessApproval\Facades\ProcessApproval::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], static function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);

            //

        });
    }
    protected function getEnvironmentSetUp($app): void
    {
        // Load the permission.php configuration file
        $permissionConfig = require workbench_path('config/permission.php');
        $app['config']->set('permission', $permissionConfig);
    }
    // Load Spatie Permission configuration

}
