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
        Schema::create('process_approval_statuses', static function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->json('steps')->nullable();
            $table->string('status', 10)->default(\RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum::CREATED->value);
            $table->foreignId('creator_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_approval_statuses');
    }
};
