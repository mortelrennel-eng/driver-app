<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enums are best updated via raw SQL in MySQL to avoid data loss
        DB::statement("ALTER TABLE incident_classifications MODIFY COLUMN behavior_mode ENUM('narrative', 'complaint', 'traffic', 'damage', 'security') DEFAULT 'narrative'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back, but note that rows with 'security' will cause warnings/truncation if not handled
        DB::statement("ALTER TABLE incident_classifications MODIFY COLUMN behavior_mode ENUM('narrative', 'complaint', 'traffic', 'damage') DEFAULT 'narrative'");
    }
};
