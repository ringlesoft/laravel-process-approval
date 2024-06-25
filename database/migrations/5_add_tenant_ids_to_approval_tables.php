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
        Schema::table('process_approval_flow_steps', static function (Blueprint $table) {
            $table->string('tenant_id', 38)->index()->nullable()->after('active');
        });
        Schema::table('process_approvals', static function (Blueprint $table) {
            $table->string('tenant_id', 38)->index()->nullable()->after('user_id');
        });
        Schema::table('process_approval_statuses', static function (Blueprint $table) {
            $table->string('tenant_id', 38)->index()->nullable()->after('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('process_approval_flow_steps', static function (Blueprint $table) {
            $table->dropColumn('total_repaid_amount');
        });
        Schema::table('process_approvals', static function (Blueprint $table) {
            $table->dropColumn('total_repaid_amount');
        });
        Schema::table('process_approval_statuses', static function (Blueprint $table) {
            $table->dropColumn('total_repaid_amount');
        });
    }
};
