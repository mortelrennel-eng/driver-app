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
        Schema::table('gps_tracking', function (Blueprint $table) {
            if (!Schema::hasColumn('gps_tracking', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('heading');
            }
            if (!Schema::hasColumn('gps_tracking', 'device_id')) {
                $table->string('device_id')->nullable()->after('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_tracking', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'device_id']);
        });
    }
};
