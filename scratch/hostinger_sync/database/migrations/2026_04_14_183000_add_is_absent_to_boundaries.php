<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->boolean('is_absent')->default(false)->after('is_extra_driver');
        });
    }

    public function down()
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn('is_absent');
        });
    }
};
