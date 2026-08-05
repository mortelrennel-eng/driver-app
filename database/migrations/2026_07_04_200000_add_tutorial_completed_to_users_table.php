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
        if (!Schema::hasColumn('users', 'tutorial_completed')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('tutorial_completed')->default(false)->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'tutorial_completed')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('tutorial_completed');
            });
        }
    }
};
