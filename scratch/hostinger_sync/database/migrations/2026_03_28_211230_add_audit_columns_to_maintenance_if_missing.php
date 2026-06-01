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
        Schema::table('maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('cost');
            }
            if (!Schema::hasColumn('maintenance', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
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
        Schema::table('maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('maintenance', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
        });
    }
};
