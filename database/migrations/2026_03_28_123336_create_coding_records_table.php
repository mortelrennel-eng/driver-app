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
        Schema::create('coding_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->date('date')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->index('unit_id');
            $table->index('date');
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
        Schema::dropIfExists('coding_records');
    }
};
