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
    public function up(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_behavior', 'boundary_id')) {
                $table->unsignedBigInteger('boundary_id')->nullable()->after('incident_date');
                // Optional foreign key, but let's avoid it in case there are constraints issues with existing tables.
                // $table->foreign('boundary_id')->references('id')->on('boundaries')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            if (Schema::hasColumn('driver_behavior', 'boundary_id')) {
                $table->dropColumn('boundary_id');
            }
        });
    }
};
