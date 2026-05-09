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
        // We use raw SQL because changing ENUM to STRING can be tricky with Blueprint sometimes depending on the driver
        DB::statement("ALTER TABLE expenses MODIFY COLUMN category VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverting to enum might lose data if new categories were added
        DB::statement("ALTER TABLE expenses MODIFY COLUMN category ENUM('Office', 'Maintenance', 'Fuel', 'Other') DEFAULT 'Other'");
    }
};
