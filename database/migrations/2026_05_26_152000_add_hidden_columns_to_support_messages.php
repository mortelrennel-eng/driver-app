<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHiddenColumnsToSupportMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->boolean('hidden_by_admin')->default(false)->after('is_read');
            $table->boolean('hidden_by_driver')->default(false)->after('hidden_by_admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn(['hidden_by_admin', 'hidden_by_driver']);
        });
    }
}
