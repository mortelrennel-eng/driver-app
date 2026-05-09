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
        $tables = ['units', 'boundaries', 'maintenance', 'drivers', 'expenses'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->unsignedBigInteger('created_by')->nullable();
                    }
                    if (!Schema::hasColumn($tableName, 'updated_by')) {
                        $table->unsignedBigInteger('updated_by')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = ['units', 'boundaries', 'maintenance', 'drivers', 'expenses'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn(['created_by', 'updated_by']);
                });
            }
        }
    }
};
