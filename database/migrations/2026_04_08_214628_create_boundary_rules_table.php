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
        Schema::create('boundary_rules', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->year('start_year');
            $blueprint->year('end_year');
            $blueprint->decimal('regular_rate', 10, 2);
            $blueprint->decimal('sat_discount', 10, 2)->default(100);
            $blueprint->decimal('sun_discount', 10, 2);
            $blueprint->decimal('coding_rate', 10, 2);
            $blueprint->boolean('coding_is_fixed')->default(false);
            $blueprint->softDeletes();
            $blueprint->timestamps();
        });

        // Insert Default Rules as requested
        DB::table('boundary_rules')->insert([
            [
                'name' => 'Legacy Models',
                'start_year' => 2012,
                'end_year' => 2013,
                'regular_rate' => 750.00,
                'sat_discount' => 100.00,
                'sun_discount' => 275.00,
                'coding_rate' => 375.00,
                'coding_is_fixed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Standard Models',
                'start_year' => 2014,
                'end_year' => 2017,
                'regular_rate' => 1100.00,
                'sat_discount' => 100.00,
                'sun_discount' => 200.00,
                'coding_rate' => 550.00,
                'coding_is_fixed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Modern Models',
                'start_year' => 2018,
                'end_year' => 2021,
                'regular_rate' => 1200.00,
                'sat_discount' => 100.00,
                'sun_discount' => 200.00,
                'coding_rate' => 550.00,
                'coding_is_fixed' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boundary_rules');
    }
};
