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
            $table->unique('browser_token');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'browser_token']);
        });
    }

    public function down()
    {
        Schema::table('user_verified_browsers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('user_verified_browsers_browser_token_unique');
            $table->dropIndex('user_verified_browsers_user_id_browser_token_index');
        });
    }
};
