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
        if (!Schema::hasColumn('maintenance', 'parts_list')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->text('parts_list')->nullable()->after('mechanic_name');
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
        if (Schema::hasColumn('maintenance', 'parts_list')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->dropColumn('parts_list');
            });
        }
    }
};
