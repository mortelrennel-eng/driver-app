<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('incident_involved_parties')) {
            Schema::create('incident_involved_parties', function (Blueprint $table) {
                $table->id();
                $table->integer('driver_behavior_id');
                $table->string('name')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('plate_number')->nullable();
                $table->timestamps();

                $table->foreign('driver_behavior_id')->references('id')->on('driver_behavior')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('incident_parts_estimates')) {
            Schema::create('incident_parts_estimates', function (Blueprint $table) {
                $table->id();
                $table->integer('driver_behavior_id');
                $table->foreignId('spare_part_id')->nullable()->constrained('spare_parts')->onDelete('set null');
                $table->string('custom_part_name')->nullable();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->timestamps();

                $table->foreign('driver_behavior_id')->references('id')->on('driver_behavior')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('incident_parts_estimates');
        Schema::dropIfExists('incident_involved_parties');
    }
};
