<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('process_approval_flows', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('approvable_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_approval_flows');
    }
};
