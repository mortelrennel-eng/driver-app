<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rescue_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('rescue_requests', 'type')) {
                $table->string('type', 20)->default('rescue')->after('unit_id');
            }
            if (!Schema::hasColumn('rescue_requests', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('rescue_requests', 'acknowledged_by')) {
                $table->unsignedBigInteger('acknowledged_by')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rescue_requests', 'acknowledged_at')) {
                $table->timestamp('acknowledged_at')->nullable()->after('acknowledged_by');
            }
        });
    }

    public function down()
    {
        Schema::table('rescue_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'photo_path', 'acknowledged_by', 'acknowledged_at']);
        });
    }
};
