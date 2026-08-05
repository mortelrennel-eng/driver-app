<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Get all active notifications across the entire system.
     * Logic synced with AppServiceProvider View Composer.
     */
    public function getGlobalNotifications()
    {
        $headerNotifications = [];
        $now = Carbon::now('Asia/Manila');
        $today = $now->toDateString();

        try {
            // 1. Flagged "At Risk" (Highest Priority)
            $flagged = DB::table('units')->whereNull('deleted_at')->where('status', 'at_risk')->get();
            foreach($flagged as $f) {
                $headerNotifications[] = [
                    'id' => 'at_risk_' . $f->id, 'type' => 'at_risk', 'title' => '🚨 Flagged: ' . $f->plate_number,
                    'message' => 'This unit is currently flagged as At Risk.', 'url' => route('units.index') . '?open_flagged=1',
                    'time' => 'Action Required', 'timestamp' => Carbon::parse($f->updated_at ?? $now)
                ];
            }

            // 2. System Alerts (Violations, Coding, Missing)
            $dbAlerts = DB::table('system_alerts')
                ->where('is_resolved', false)->orderByDesc('created_at')->limit(30)->get();
            foreach($dbAlerts as $a) {
                $headerNotifications[] = [
                    'id' => $a->id, 'type' => 'violation_alert', 'title' => $a->title, 'message' => $a->message,
                    'url' => ($a->type === 'missing_unit' || $a->type === 'coding_notice') ? route('units.index') . '?open_flagged=1' : route('driver-behavior.index'),
                    'time' => Carbon::parse($a->created_at)->diffForHumans(), 'timestamp' => Carbon::parse($a->created_at)
                ];
            }

            // 3. Franchise Renewals
            $cases = DB::table('franchise_cases')->whereNull('deleted_at')->whereNotNull('expiry_date')->get();
            foreach ($cases as $c) {
                $expDt = Carbon::parse($c->expiry_date);
                if ($expDt->isPast() || $expDt->isBetween($now, $now->copy()->addYear())) {
                    $isExpired = $expDt->isPast();
                    $headerNotifications[] = [
                        'type' => 'case_expiry', 'title' => $isExpired ? 'Expired Franchise' : 'Franchise Renewal',
                        'message' => 'Case ' . $c->case_no . ' (' . $c->applicant_name . ') ' . ($isExpired ? 'expired on ' : 'expires on ') . $expDt->format('M d, Y'),
                        'url' => route('decision-management.index'), 'time' => $isExpired ? 'NOW' : 'Upcoming', 'timestamp' => $expDt
                    ];
                }
            }

            // 4. Maintenance Today
            $todayMaint = DB::table('maintenance')
                ->join('units', 'maintenance.unit_id', '=', 'units.id')->whereNull('maintenance.deleted_at')
                ->where('maintenance.date_started', $today)->where('maintenance.status', '!=', 'completed')
                ->select('maintenance.id', 'units.plate_number', 'maintenance.maintenance_type')->get();
            foreach($todayMaint as $tm) {
                $headerNotifications[] = [
                    'type' => 'maintenance_today', 'title' => 'Maintenance Today', 'message' => "Unit {$tm->plate_number} schedule: " . ucfirst($tm->maintenance_type),
                    'url' => route('maintenance.index', ['search' => $tm->plate_number]), 'time' => 'Today', 'timestamp' => $now
                ];
            }

            // 5. Low Stock Spare Parts
            $lowStock = DB::table('spare_parts')->where('stock_quantity', '<=', 5)->limit(5)->get();
            foreach ($lowStock as $p) {
                $qty = (int)$p->stock_quantity;
                $headerNotifications[] = [
                    'type' => 'low_stock', 'title' => ($qty === 0 ? '⚠ OUT OF STOCK: ' : '⚠ Low Stock: ') . $p->name,
                    'message' => "Stock: {$qty} items. Source: " . ($p->supplier ?? 'Unspecified'), 'url' => route('maintenance.index', ['open_inventory' => 1]),
                    'time' => $qty === 0 ? 'REORDER NOW' : 'Critical', 'timestamp' => Carbon::parse($p->updated_at ?? $now)
                ];
            }

            // 6. Recent Driver Behavior Incidents (Excluding coding duplicates)
            $recentIncidents = DB::table('driver_behavior as db')
                ->join('drivers as d', 'db.driver_id', '=', 'd.id')
                
                ->where('db.created_at', '>=', $now->copy()->subDays(3))
                ->select('db.*', DB::raw("CONCAT(d.first_name, ' ', d.last_name) as driver_name"))
                ->orderByDesc('db.created_at')
                ->limit(10)
                ->get();
            foreach ($recentIncidents as $ri) {
                $headerNotifications[] = [
                    'type' => 'driver_incident', 'title' => 'Driver Incident: ' . ($ri->incident_type ?: 'Warning'),
                    'message' => "{$ri->driver_name}: {$ri->description}", 'url' => route('driver-behavior.index'),
                    'time' => Carbon::parse($ri->created_at)->diffForHumans(), 'timestamp' => Carbon::parse($ri->created_at)
                ];
            }

            // 7. Driver License Expiry
            $expiringDrivers = DB::table('drivers')
                ->whereNull('deleted_at')
                ->where('license_expiry', '<=', $now->copy()->addDays(30)->toDateString())
                ->get();
            foreach ($expiringDrivers as $ed) {
                $expDt = Carbon::parse($ed->license_expiry);
                $isExpired = $expDt->isPast();
                $driverName = trim(($ed->first_name ?? '') . ' ' . ($ed->last_name ?? ''));
                $headerNotifications[] = [
                    'id' => 'lic_exp_' . $ed->id,
                    'type' => 'license_expiry',
                    'title' => $isExpired ? '🚫 Expired License: ' . $driverName : '⚠️ License Renewal: ' . $driverName,
                    'message' => "{$driverName}'s license " . ($isExpired ? 'expired on ' : 'expires on ') . $expDt->format('M d, Y') . ". Please update the record.",
                    'url' => route('driver-management.index') . '?edit_driver=' . $ed->id,
                    'time' => $isExpired ? 'ACTION REQUIRED' : 'Upcoming',
                    'timestamp' => $expDt
                ];
            }

            // --- SORTING ---
            usort($headerNotifications, function($a, $b) {
                $prioA = (isset($a['time']) && in_array(strtoupper($a['time']), ['ACTION REQUIRED', 'REORDER NOW', 'NOW', 'CRITICAL'])) ? 1 : 0;
                $prioB = (isset($b['time']) && in_array(strtoupper($b['time']), ['ACTION REQUIRED', 'REORDER NOW', 'NOW', 'CRITICAL'])) ? 1 : 0;
                if ($prioA !== $prioB) return $prioB - $prioA;
                
                $timeA = isset($a['timestamp']) ? (is_object($a['timestamp']) ? $a['timestamp']->timestamp : strtotime($a['timestamp'])) : 0;
                $timeB = isset($b['timestamp']) ? (is_object($b['timestamp']) ? $b['timestamp']->timestamp : strtotime($b['timestamp'])) : 0;
                return $timeB - $timeA;
            });

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('NotificationService Error: ' . $e->getMessage());
        }

        return $headerNotifications;
    }
}
