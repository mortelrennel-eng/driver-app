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
        // 1. Drop old constraint (wrapped in try-catch in case it was already dropped)
        try {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE drivers DROP FOREIGN KEY drivers_ibfk_1');
        } catch (\Exception $e) {}

        // 2. Make column nullable
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE drivers MODIFY user_id BIGINT UNSIGNED NULL');
        
        // Removed adding foreign key constraint to avoid MySQL 150 errors and just rely on logical linking
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
