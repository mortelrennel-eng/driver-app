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
        Schema::create('gps_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->string('heading')->nullable();
            $table->boolean('ignition_status')->nullable();
            $table->timestamp('timestamp')->nullable();
            $table->timestamps();
            $table->index('unit_id');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gps_tracking');
    }
};
