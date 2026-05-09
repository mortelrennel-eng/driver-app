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
        try {
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant', 'surveillance') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
            \Log::error("Failed to update units status enum: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
        }
    }
};
