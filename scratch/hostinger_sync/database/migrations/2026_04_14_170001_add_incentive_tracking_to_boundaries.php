<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->boolean('counted_for_incentive')->default(true)->after('has_incentive');
            $table->date('incentive_released_at')->nullable()->after('counted_for_incentive');
        });
    }

    public function down()
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn(['counted_for_incentive', 'incentive_released_at']);
        });
    }
};
