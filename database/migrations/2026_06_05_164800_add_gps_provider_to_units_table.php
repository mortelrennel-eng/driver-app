<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'gps_provider')) {
                $table->string('gps_provider', 20)->nullable()->default('tracksolid')->after('imei')
                      ->comment('GPS provider type: tracksolid or aksh');
            }
            if (!Schema::hasColumn('units', 'gps_password')) {
                $table->string('gps_password', 50)->nullable()->after('gps_provider')
                      ->comment('Custom GPS device password, falls back to env default');
            }
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'gps_provider')) {
                $table->dropColumn('gps_provider');
            }
            if (Schema::hasColumn('units', 'gps_password')) {
                $table->dropColumn('gps_password');
            }
        });
    }
};
