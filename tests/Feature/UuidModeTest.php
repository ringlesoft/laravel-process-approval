<?php

namespace RingleSoft\LaravelProcessApproval\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalStatus;
use RingleSoft\LaravelProcessApproval\Tests\TestCase;

class UuidModeTest extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('process_approval.use_uuids', true);
        $app['config']->set('process_approval.load_migrations', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('uuid_test_models')) {
            Schema::create('uuid_test_models', static function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }

    public function testPackageModelsGenerateUuidPrimaryKeysWhenEnabled(): void
    {
        $flow = ProcessApprovalFlow::query()->create([
            'name' => 'Test Flow',
            'approvable_type' => UuidTestModel::class,
        ]);

        $this->assertTrue(Str::isUuid((string) $flow->id));
        $this->assertFalse($flow->getIncrementing());
        $this->assertSame('string', $flow->getKeyType());

        $roleId = (string) Str::uuid();

        $step = $flow->steps()->create([
            'role_id' => $roleId,
            'action' => 'APPROVE',
            'active' => 1,
        ]);

        $this->assertInstanceOf(ProcessApprovalFlowStep::class, $step);
        $this->assertTrue(Str::isUuid((string) $step->id));
        $this->assertSame($roleId, $step->role_id);
        $this->assertFalse($step->getIncrementing());
        $this->assertSame('string', $step->getKeyType());
    }

    public function testUuidMorphColumnsCanStoreUuidApprovableIds(): void
    {
        $model = UuidTestModel::query()->create(['name' => 'Record']);

        $this->assertTrue(Str::isUuid((string) $model->id));

        $status = ProcessApprovalStatus::query()->create([
            'approvable_type' => UuidTestModel::class,
            'approvable_id' => $model->id,
            'steps' => [],
            'status' => 'Created',
            'creator_id' => null,
        ]);

        $this->assertTrue(Str::isUuid((string) $status->id));
        $this->assertSame((string) $model->id, (string) $status->approvable_id);

        $resolved = $status->approvable;
        $this->assertInstanceOf(UuidTestModel::class, $resolved);
        $this->assertSame((string) $model->id, (string) $resolved->id);
    }
}

class UuidTestModel extends Model
{
    protected $table = 'uuid_test_models';

    protected $guarded = [];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(static function (self $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
