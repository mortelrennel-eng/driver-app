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
        Schema::table('units', function (Blueprint $table) {
            $table->timestamp('shift_deadline_at')->nullable()->after('last_swapping_at');
        });

        // Initialize shift_deadline_at exactly 24 hours from now for any unit without one
        DB::table('units')->whereNull('shift_deadline_at')->update([
            'shift_deadline_at' => now()->addHours(24)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('shift_deadline_at');
        });
    }
};
