<?php

namespace App\Traits;

use App\Models\Driver;
use App\Models\DriverBehavior;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait CalculatesDriverPerformance
{
    /**
     * Determine if a driver is eligible for an incentive on a specific date.
     * Logic: Must have 0 performance-impacting incidents on that date.
     */
    public function isEligibleToday(Driver $driver, $date = null)
    {
        $date = $date ?: now()->toDateString();
        
        $hasViolation = DriverBehavior::where('driver_id', $driver->id)
            ->whereDate('incident_date', $date)
            ->violations()
            ->exists();
            
        return !$hasViolation;
    }

    /**
     * Calculate the 360-degree performance rating for a driver.
     * Centralized logic used by both Index and Show views.
     */
    public function calculatePerformanceRating($driver)
    {
        // Handle both Eloquent models and StdClass from DB queries
        $shiftsCount    = (int) data_get($driver, 'shifts_count', 0);
        $paidCount      = (int) data_get($driver, 'paid_shifts_count', 0);
        $totalPaidCount = (int) data_get($driver, 'total_paid_count', 0);
        $incidentsCount = (int) data_get($driver, 'incidents_count', 0);
        $missedCount    = (int) data_get($driver, 'missed_incentive_count', 0);
        $absentCount    = (int) data_get($driver, 'absent_count', 0);
        
        $netShortage    = (float) data_get($driver, 'net_shortage', 0);
        $pendingDebt    = (float) data_get($driver, 'total_pending_debt', 0);
        
        $hasShortage    = $netShortage > 0;
        $hasDebt        = $pendingDebt > 0;

        // --- FRESH DRIVER LOGIC: Never had a paid shift in history ---
        if ($totalPaidCount === 0) {
            if ($hasDebt) return ['label' => 'At Risk', 'stars' => 1];
            return ['label' => 'New Driver', 'stars' => 0];
        }

        // --- ACTIVE DRIVER LOGIC: Has history but 0 shifts in 30 days ---
        if ($shiftsCount === 0) {
            return ['label' => 'On Break', 'stars' => 0];
        }

        // --- PERFORMANCE LADDER ---
        // Eligibility for high ratings requires 0 violations in the last 30 days
        $isEligible = ($incidentsCount === 0 && $missedCount === 0 && $absentCount === 0 && !$hasShortage && !$hasDebt);

        if ($isEligible) {
            if ($paidCount >= 25) return ['label' => 'Elite', 'stars' => 5];
            if ($paidCount >= 15) return ['label' => 'Excellent', 'stars' => 4];
            if ($paidCount >= 5)  return ['label' => 'Good', 'stars' => 3];
            return ['label' => 'Growing', 'stars' => 2];
        } else {
            // Penalties for debts or multiple incidents
            if ($hasDebt || $incidentsCount >= 2) return ['label' => 'At Risk', 'stars' => 1];
            return ['label' => 'Average', 'stars' => 2];
        }
    }

    /**
     * Get a standardized SQL snippet for counting performance-impacting incidents.
     * Useful for raw DB queries to maintain consistency with the DriverBehavior scope.
     */
    public function getViolationQuerySnippet()
    {
        $types = "'" . implode("','", DriverBehavior::VIOLATION_TYPES) . "'";
        return "(is_driver_fault = 1 OR incident_type IN ($types))";
    }

    /**
     * Centralized check for ANY violation (Behavioral or Boundary-based)
     */
    public function getViolationCount(Driver $driver, $dateFrom = null, $dateTo = null, $includeUnreleasedOnly = false)
    {
        $behaviorQuery = DriverBehavior::where('driver_id', $driver->id)->violations();
        $boundaryQuery = DB::table('boundaries')->where('driver_id', $driver->id)
            ->where(function($q) {
                $q->where('shortage', '>', 0)
                  ->orWhere('has_incentive', false)
                  ->orWhere('is_absent', true);
            });

        if ($dateFrom) {
            $behaviorQuery->whereDate('incident_date', '>=', $dateFrom);
            $boundaryQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $behaviorQuery->whereDate('incident_date', '<=', $dateTo);
            $boundaryQuery->whereDate('date', '<=', $dateTo);
        }
        if ($includeUnreleasedOnly) {
            $behaviorQuery->whereNull('incentive_released_at');
            $boundaryQuery->whereNull('incentive_released_at');
        }

        return $behaviorQuery->count() + $boundaryQuery->count();
    }
}
