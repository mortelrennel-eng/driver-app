<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->date('incentive_released_at')->nullable()->after('total_charge_to_driver');
        });
    }

    public function down()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->dropColumn('incentive_released_at');
        });
    }
};
