<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure boundaries table exists and has correct columns
        if (!Schema::hasTable('boundaries')) {
            Schema::create('boundaries', function (Blueprint $table) {
                $table->id();
                $table->integer('unit_id');
                $table->integer('driver_id');
                $table->date('date');
                $table->decimal('boundary_amount', 10, 2);
                $table->enum('status', ['pending', 'paid', 'excess', 'short'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['unit_id', 'date']);
                $table->index(['driver_id', 'date']);
                $table->index('status');
            });
        } else {
            // Add missing columns if they don't exist
            Schema::table('boundaries', function (Blueprint $table) {
                if (!Schema::hasColumn('boundaries', 'status')) {
                    $table->enum('status', ['pending', 'paid', 'excess', 'short'])->default('pending')->after('boundary_amount');
                }
                if (!Schema::hasColumn('boundaries', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
                if (!Schema::hasColumn('boundaries', 'created_at')) {
                    $table->timestamps();
                }
            });
        }

        // Ensure units table has status column
        if (Schema::hasTable('units') && !Schema::hasColumn('units', 'status')) {
            Schema::table('units', function (Blueprint $table) {
                $table->enum('status', ['active', 'maintenance', 'coding', 'retired'])->default('active')->after('model');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boundaries');
    }
};
