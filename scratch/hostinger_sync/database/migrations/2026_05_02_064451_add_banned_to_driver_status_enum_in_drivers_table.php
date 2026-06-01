<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE drivers MODIFY COLUMN driver_status ENUM('available', 'assigned', 'on_leave', 'suspended', 'banned') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE drivers MODIFY COLUMN driver_status ENUM('available', 'assigned', 'on_leave', 'suspended') DEFAULT 'available'");
    }
};
