<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $row) {
            $row->boolean('is_disabled')->default(false)->after('is_active');
            $row->text('disable_reason')->nullable()->after('is_disabled');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $row) {
            $row->dropColumn(['is_disabled', 'disable_reason']);
        });
    }
};
