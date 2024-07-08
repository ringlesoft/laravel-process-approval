<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalTypeEnum;
use Workbench\App\Models\TestModel;

class TestModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestModel::seedSteps();
    }
}
