<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. DYNAMIC PRE-MIGRATION CLEANUP (Self-Healing Financial Garbage Collector):
        // Scan the database for any pre-existing duplicate active boundary records.
        // For any duplicates found, we keep the newest one and safely soft-delete (archive) the others.
        $duplicates = DB::table('boundaries')
            ->select('unit_id', 'date', DB::raw('COUNT(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('unit_id', 'date')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $records = DB::table('boundaries')
                ->where('unit_id', $dup->unit_id)
                ->where('date', $dup->date)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc') // Keep the most recent record
                ->get();

            if (count($records) > 1) {
                $keepId = $records[0]->id;
                DB::table('boundaries')
                    ->where('unit_id', $dup->unit_id)
                    ->where('date', $dup->date)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $keepId)
                    ->update([
                        'deleted_at' => now(),
                        'notes' => DB::raw("CONCAT(IFNULL(notes, ''), ' [System Hardening Auto-Archive: Concurrency Duplicate Resolved]')")
                    ]);
            }
        }

        // 2. APPLY UNIQUE CONSTRAINT:
        Schema::table('boundaries', function (Blueprint $table) {
            if (!Schema::hasColumn('boundaries', 'active_date')) {
                // Add virtual column computed as the date ONLY if deleted_at is NULL.
                // This enables unique indexes to ignore multiple soft-deleted/archived records (since MySQL allows multiple NULL values in a unique index).
                $table->string('active_date')->virtualAs('IF(deleted_at IS NULL, date, NULL)')->nullable();
                $table->unique(['unit_id', 'active_date'], 'boundaries_active_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boundaries', function (Blueprint $table) {
            if (Schema::hasColumn('boundaries', 'active_date')) {
                $table->dropUnique('boundaries_active_unique');
                $table->dropColumn('active_date');
            }
        });
    }
};
