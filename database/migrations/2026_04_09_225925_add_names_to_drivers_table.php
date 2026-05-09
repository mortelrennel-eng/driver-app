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
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('nickname')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('drivers', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('drivers', 'nickname')) {
                $table->dropColumn('nickname');
            }
        });
    }
};
