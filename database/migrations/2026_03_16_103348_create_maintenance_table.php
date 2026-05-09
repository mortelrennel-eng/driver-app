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
        Schema::create('maintenance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->string('maintenance_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->integer('odometer_reading')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_completed')->nullable();
            $table->string('status')->default('pending');
            $table->string('mechanic_name')->nullable();
            $table->text('parts_list')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();

            $table->index('unit_id');
            $table->index('date_started');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance');
    }
};
