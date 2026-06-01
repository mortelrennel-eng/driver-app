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
        Schema::table('user_verified_browsers', function (Blueprint $table) {
            if (!Schema::hasColumn('user_verified_browsers', 'device_info')) {
                $table->text('device_info')->nullable()->after('user_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_verified_browsers', function (Blueprint $table) {
            $table->dropColumn('device_info');
        });
    }
};
