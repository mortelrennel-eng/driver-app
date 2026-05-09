<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('license_photo')->nullable()->after('license_expiry');
            $table->string('nbi_clearance_photo')->nullable()->after('license_photo');
            $table->string('pnp_clearance_photo')->nullable()->after('nbi_clearance_photo');
            $table->string('profile_photo')->nullable()->after('nickname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['license_photo', 'nbi_clearance_photo', 'pnp_clearance_photo', 'profile_photo']);
        });
    }
};
