<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedBigInteger('current_turn_driver_id')->nullable()->after('secondary_driver_id');
            $table->timestamp('last_swapping_at')->nullable()->after('current_turn_driver_id');
        });

        Schema::table('boundaries', function (Blueprint $table) {
            $table->unsignedBigInteger('expected_driver_id')->nullable()->after('driver_id');
            $table->boolean('has_incentive')->default(true)->after('status');
        });

        // Initialize existing units with primary driver as initial turn holder
        DB::table('units')->update([
            'current_turn_driver_id' => DB::raw('driver_id'),
            'last_swapping_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['current_turn_driver_id', 'last_swapping_at']);
        });

        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn(['expected_driver_id', 'has_incentive']);
        });
    }
};
