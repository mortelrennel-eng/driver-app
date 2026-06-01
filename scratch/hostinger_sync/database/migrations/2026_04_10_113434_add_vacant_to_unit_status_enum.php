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
        // Add 'vacant' to units table status enum
        try {
            DB::statement("ALTER TABLE units MODIFY status ENUM('active', 'maintenance', 'coding', 'retired', 'vacant') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
            // Fallback or ignore if not MySQL/MariaDB
            Log::error("Failed to update units status enum: " . $e->getMessage());
        }

        // Add is_active to drivers table
        if (!Schema::hasColumn('drivers', 'is_active')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('status');
            });
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
            DB::statement("ALTER TABLE units MODIFY COLUMN status ENUM('active', 'maintenance', 'coding', 'retired') NOT NULL DEFAULT 'active'");
        } catch (\Exception $e) {
        }

        if (Schema::hasColumn('drivers', 'is_active')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
