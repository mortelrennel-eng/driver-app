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
            if (!Schema::hasColumn('expenses', 'vendor_name')) {
                $table->string('vendor_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('expenses', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('amount');
            }
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
            $table->dropColumn(['vendor_name', 'payment_method']);
        });
    }
};
