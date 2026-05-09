<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Account approval workflow
            if (!Schema::hasColumn('users', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                      ->default('approved')
                      ->after('role');
            }
            if (!Schema::hasColumn('users', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('users', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            // Per-page access control (JSON array of route names)
            if (!Schema::hasColumn('users', 'allowed_pages')) {
                $table->json('allowed_pages')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'allowed_pages']);
        });
    }
};
