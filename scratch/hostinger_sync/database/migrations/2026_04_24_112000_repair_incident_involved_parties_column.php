<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('incident_involved_parties')) {
            // Check if 'incident_id' exists and 'driver_behavior_id' does not
            if (Schema::hasColumn('incident_involved_parties', 'incident_id') && !Schema::hasColumn('incident_involved_parties', 'driver_behavior_id')) {
                Schema::table('incident_involved_parties', function (Blueprint $table) {
                    $table->renameColumn('incident_id', 'driver_behavior_id');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('incident_involved_parties')) {
            if (Schema::hasColumn('incident_involved_parties', 'driver_behavior_id')) {
                Schema::table('incident_involved_parties', function (Blueprint $table) {
                    $table->renameColumn('driver_behavior_id', 'incident_id');
                });
            }
        }
    }
};
