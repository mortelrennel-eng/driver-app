<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'gps_imei')) {
                $table->string('gps_imei', 20)->nullable()->after('gps_device_count')
                      ->comment('TracksolidPro device IMEI/ID');
            }
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('gps_imei');
        });
    }
};
