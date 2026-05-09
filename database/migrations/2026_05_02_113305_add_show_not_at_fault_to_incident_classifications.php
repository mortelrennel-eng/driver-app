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
        Schema::table('incident_classifications', function (Blueprint $table) {
            $table->boolean('show_not_at_fault')->default(false)->after('behavior_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_classifications', function (Blueprint $table) {
            $table->dropColumn('show_not_at_fault');
        });
    }
};
