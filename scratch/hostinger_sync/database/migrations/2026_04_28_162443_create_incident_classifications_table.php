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
        Schema::create('incident_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('default_severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('color')->default('gray');
            $table->string('icon')->default('alert-circle');
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
        Schema::dropIfExists('incident_classifications');
    }
};
