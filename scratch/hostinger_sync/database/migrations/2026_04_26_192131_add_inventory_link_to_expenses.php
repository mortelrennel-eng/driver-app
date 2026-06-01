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
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('spare_part_id')->nullable()->after('unit_id');
            $table->integer('quantity')->nullable()->after('amount');
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity');
            
            // Optional: Index for better filtering/linking
            $table->index('spare_part_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['spare_part_id', 'quantity', 'unit_price']);
        });
    }
};
