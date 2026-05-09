<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_behavior', 'missing_days_reported')) {
                $table->unsignedSmallInteger('missing_days_reported')->nullable();
            }
            if (!Schema::hasColumn('driver_behavior', 'stolen_driver_detail_name')) {
                $table->string('stolen_driver_detail_name', 255)->nullable();
            }
            if (!Schema::hasColumn('driver_behavior', 'stolen_driver_detail_contact')) {
                $table->string('stolen_driver_detail_contact', 64)->nullable();
            }
            if (!Schema::hasColumn('driver_behavior', 'stolen_driver_license_no')) {
                $table->string('stolen_driver_license_no', 64)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_behavior', function (Blueprint $table) {
            foreach ([
                'missing_days_reported',
                'stolen_driver_detail_name',
                'stolen_driver_detail_contact',
                'stolen_driver_license_no',
            ] as $col) {
                if (Schema::hasColumn('driver_behavior', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
