<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_approval_statuses', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('approvable');
            $table->json('steps')->nullable();
            $table->string('status', 10)->default(ApprovalStatusEnum::CREATED->value);
            $table->uuid('creator_id')->nullable();
            $table->string('tenant_id', 38)->index()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_approval_statuses');
    }
};
