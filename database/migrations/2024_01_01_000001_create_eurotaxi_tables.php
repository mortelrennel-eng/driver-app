<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration is safe to run against the existing EUROTAXI database.
 * It only ADDS columns to existing tables and creates tables that don't exist.
 * All operations use hasTable / hasColumn guards.
 */
return new class extends Migration {
    public function up(): void
    {
        // ─── Extend users table with Laravel + app-specific columns ───
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->unique()->nullable();
            }
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'manager', 'driver', 'staff'])->default('staff');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
        });

        // ─── System Alerts (new table, may not exist) ─────────────────
        if (!Schema::hasTable('system_alerts')) {
            Schema::create('system_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('alert_type', 50)->default('other');
                $table->string('title', 255);
                $table->text('message');
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
                $table->integer('unit_id')->nullable();
                $table->integer('driver_id')->nullable();
                $table->boolean('is_read')->default(false);
                $table->boolean('is_resolved')->default(false);
                $table->integer('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // ─── Franchise Cases (new table, may not exist) ───────────────
        if (!Schema::hasTable('franchise_cases')) {
            Schema::create('franchise_cases', function (Blueprint $table) {
                $table->id();
                $table->string('case_no', 50)->unique();
                $table->string('applicant_name', 100);
                $table->integer('unit_id')->nullable();
                $table->date('filing_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('franchise_cases');
    }
};
