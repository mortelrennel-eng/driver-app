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
            $table->softDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('boundaries', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('maintenance', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('franchise_cases', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->softDeletes();
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
            $table->dropSoftDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('maintenance', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('franchise_cases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
