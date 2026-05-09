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
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->string('motor_no')->nullable()->after('model');
            $table->string('chassis_no')->nullable()->after('motor_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->dropColumn('motor_no');
            $table->dropColumn('chassis_no');
        });
    }
};
