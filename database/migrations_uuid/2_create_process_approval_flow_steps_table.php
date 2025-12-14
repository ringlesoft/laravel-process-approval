<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_approval_flow_steps', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('process_approval_flow_id')->constrained('process_approval_flows')->cascadeOnDelete();
            $table->uuid('role_id')->index();
            $table->json('permissions')->nullable();
            $table->integer('order')->nullable()->index();
            $table->enum('action', ['APPROVE', 'VERIFY', 'CHECK'])->default('APPROVE');
            $table->tinyInteger('active')->default(1);
            $table->string('tenant_id', 38)->index()->nullable()->after('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_approval_flow_steps');
    }
};
