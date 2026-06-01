<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to avoid doctrine/dbal dependency for column changes
        DB::statement("ALTER TABLE `driver_behavior` MODIFY `incident_type` VARCHAR(191) NULL");
        DB::statement("ALTER TABLE `driver_behavior` MODIFY `severity` VARCHAR(191) NULL");
        DB::statement("ALTER TABLE `driver_behavior` MODIFY `charge_status` VARCHAR(191) NULL DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback not strictly necessary since we're relaxing constraints
    }
};
