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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->enum('status', ['active', 'maintenance', 'coding', 'retired'])->default('active');
            $table->decimal('boundary_rate', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('fuel_status')->nullable();
            $table->integer('coding_day')->nullable();
            $table->integer('driver_id')->nullable();
            $table->integer('secondary_driver_id')->nullable();
            $table->boolean('gps_enabled')->default(false);
            $table->boolean('dashcam_enabled')->default(false);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('current_location')->nullable();
            $table->timestamp('last_location_update')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('units');
    }
};
