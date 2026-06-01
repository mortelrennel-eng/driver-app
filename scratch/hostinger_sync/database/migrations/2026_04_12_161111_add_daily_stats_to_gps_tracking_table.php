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
            $table->decimal('odo', 12, 2)->nullable()->after('ignition_status');
            $table->decimal('daily_start_mileage', 12, 2)->nullable()->after('odo');
            $table->date('daily_start_date')->nullable()->after('daily_start_mileage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_tracking', function (Blueprint $table) {
            $table->dropColumn(['odo', 'daily_start_mileage', 'daily_start_date']);
        });
    }
};
