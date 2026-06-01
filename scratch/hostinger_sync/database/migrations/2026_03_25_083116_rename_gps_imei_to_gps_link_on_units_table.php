<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable strict mode temporarily to bypass '0000-00-00' date errors in MariaDB
        \Illuminate\Support\Facades\DB::statement("SET SESSION sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
        
        // Use raw SQL to avoid MariaDB renameColumn issues
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE units CHANGE gps_imei gps_link TEXT NULL');
    }

    public function down()
    {
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE units CHANGE gps_link gps_imei VARCHAR(255) NULL');
    }
};
