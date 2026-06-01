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
    public function up(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            // Drop the old constraint pointing to users table
            $table->dropForeign('driver_behavior_ibfk_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            //
        });
    }
};
