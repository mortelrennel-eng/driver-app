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
    public function up(): void
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->decimal('actual_boundary', 10, 2)->nullable()->after('boundary_amount');
            $table->decimal('shortage', 10, 2)->default(0)->after('actual_boundary');
            $table->decimal('excess', 10, 2)->default(0)->after('shortage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('boundaries', function (Blueprint $table) {
            $table->dropColumn(['actual_boundary', 'shortage', 'excess']);
        });
    }
};
