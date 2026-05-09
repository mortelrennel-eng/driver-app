<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('franchise_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('franchise_cases', 'status')) {
                $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending')->after('expiry_date');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('franchise_cases', 'status')) {
            Schema::table('franchise_cases', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
