<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_approvals', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('approvable');
            $table->foreignUuid('process_approval_flow_step_id')->nullable()->constrained('process_approval_flow_steps')->cascadeOnDelete();
            $table->string('approval_action', 12)->default('Approved');
            $table->text('approver_name')->nullable();
            $table->text('comment')->nullable();
            $table->foreignUuid('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('tenant_id', 38)->index()->nullable()->after('user_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_approvals');
    }
};
