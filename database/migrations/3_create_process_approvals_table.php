<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('process_approvals', static function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('process_approval_flow_step_id')->nullable()->references('id')->on('process_approval_flow_steps')->cascadeOnDelete();
            $table->string('approval_action', 12)->default('Approved');
            $table->text('approver_name')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_approvals');
    }
};
