<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            // 1. Add 'at_risk' to the ENUM
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant', 'surveillance', 'at_risk') NOT NULL DEFAULT 'active'");
            
            // 2. Migrate data
            DB::table('units')->where('status', 'surveillance')->update(['status' => 'at_risk']);
            
            // 3. Remove 'surveillance' from the ENUM
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant', 'at_risk') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
            \Log::error("Failed to rename surveillance to at_risk: " . $e->getMessage());
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
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant', 'at_risk', 'surveillance') NOT NULL DEFAULT 'active'");
            DB::table('units')->where('status', 'at_risk')->update(['status' => 'surveillance']);
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant', 'surveillance') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
        }
    }
};
