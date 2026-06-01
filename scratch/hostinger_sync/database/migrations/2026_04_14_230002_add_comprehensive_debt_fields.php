<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->string('cause_of_incident')->nullable()->after('incident_type');
            $table->decimal('total_paid', 10, 2)->default(0)->after('total_charge_to_driver');
            $table->decimal('remaining_balance', 10, 2)->default(0)->after('total_paid');
        });

        Schema::table('boundaries', function (Blueprint $table) {
            $table->decimal('damage_payment', 10, 2)->default(0)->after('actual_boundary');
        });
    }

    public function down()
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            $table->dropColumn(['cause_of_incident', 'total_paid', 'remaining_balance']);
        });

        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn('damage_payment');
        });
    }
};
