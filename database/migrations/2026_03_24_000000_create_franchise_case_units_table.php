<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('franchise_case_units', function (Blueprint $row) {
            $row->id();
            $row->integer('franchise_case_id');
            $row->string('make')->nullable();
            $row->string('motor_no')->nullable();
            $row->string('chasis_no')->nullable();
            $row->string('plate_no')->nullable();
            $row->string('year_model')->nullable();
            $row->timestamps();
            
            $row->foreign('franchise_case_id')->references('id')->on('franchise_cases')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('franchise_case_units');
    }
};
