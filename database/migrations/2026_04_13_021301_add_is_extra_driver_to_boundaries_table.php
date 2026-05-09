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
        Schema::table('boundaries', function (Blueprint $table) {
            $table->boolean('is_extra_driver')->default(false)->after('actual_boundary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn('is_extra_driver');
        });
    }
};
