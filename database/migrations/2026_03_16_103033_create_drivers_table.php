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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('license_number')->unique();
            $table->date('license_expiry')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('daily_boundary_target', 12, 2)->nullable();
            $table->string('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('driver_type', ['regular', 'senior', 'trainee'])->default('regular');
            $table->enum('driver_status', ['available', 'assigned', 'on_leave', 'suspended'])->default('available');
            $table->string('designation')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('driver_status');
            $table->index('license_expiry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
