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
        // Drop unique index
        DB::statement('ALTER TABLE units DROP INDEX unit_number');
        
        // Make nullable
        DB::statement('ALTER TABLE units MODIFY COLUMN unit_number VARCHAR(255) NULL');
    }

    public function down()
    {
        // Re-add unique and not null
        DB::statement('ALTER TABLE units MODIFY COLUMN unit_number VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE units ADD UNIQUE (unit_number)');
    }
};
