<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Carbon::setLocale('en');
        date_default_timezone_set('Asia/Manila');

        // Fix for shared hosting MAX_JOIN_SIZE limitation
        // Allows complex queries with multiple JOINs to run without hitting row limits
        try {
            \Illuminate\Support\Facades\DB::statement('SET SQL_BIG_SELECTS=1');
        } catch (\Exception $e) {
            // Silent fail if DB not yet available (e.g. during migrations)
        }

        // Auto-intercept and send Realtime FCM Push Notifications for all raw system_alerts table inserts
        try {
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                if (str_contains($query->sql, 'insert into `system_alerts`')) {
                    try {
                        $bindings = $query->bindings;
                        $title = 'System Alert';
                        $message = 'A new system alert has been registered.';

                        // Extract column name mapping to bindings from the raw insert SQL statement
                        preg_match_all('/`([a-zA-Z0-9_]+)`/', $query->sql, $matches);
                        if (!empty($matches[1])) {
                            // First match is the table name 'system_alerts' so slice it off
                            $columns = array_values(array_filter($matches[1], fn($col) => $col !== 'system_alerts'));
                            foreach ($columns as $idx => $col) {
                                if ($col === 'title' && isset($bindings[$idx])) {
                                    $title = $bindings[$idx];
                                }
                                if ($col === 'message' && isset($bindings[$idx])) {
                                    $message = $bindings[$idx];
                                }
                            }
                        }

                        // Broadcast to all users with active FCM tokens
                        $tokens = \Illuminate\Support\Facades\DB::table('users')
                            ->whereNotNull('fcm_token')
                            ->where('fcm_token', '!=', '')
                            ->pluck('fcm_token')
                            ->unique();

                        foreach ($tokens as $token) {
                            \App\Services\FirebasePushService::sendPush($title, $message, $token, 'system_alert');
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('FCM Interceptor Error: ' . $e->getMessage());
                    }
                }
            });
        } catch (\Exception $e) {
            // Silence
        }

        // Global Notifications for Franchise Expirations
        // Global Notifications for Franchise Expirations and Maintenance
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (!auth()->check()) {
                return;
            }

            try {
                $user = auth()->user();
                $userId = $user->id;
                
                $data = [
                    'expiringFranchise' => [],
                    'maintNotifs' => [],
                    'odoMaintNotifs' => [],
                    'stockNotifs' => [],
                    'licenseNotifs' => []
                ];

                // 1. Global Notifications for Franchise Expirations
                if ($user->hasAccessTo('decision-management.*')) {
                    $cases = \Illuminate\Support\Facades\DB::table('franchise_cases')
                        ->whereNull('deleted_at')
                        ->whereNotNull('expiry_date')
                        ->get();
                    
                    $today = Carbon::today();
                    $nextYear = Carbon::today()->addYear();
                    
                    foreach ($cases as $c) {
                        $expDt = Carbon::parse($c->expiry_date);
                        if ($expDt->isPast()) {
                            $data['expiringFranchise'][] = [
                                'type' => 'case_expiry',
                                'title' => 'Expired Franchise Alert',
                                'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') has already expired on ' . $expDt->format('M d, Y') . '.',
                                'url' => route('decision-management.index')
                            ];
                        } elseif ($expDt->isBetween($today, $nextYear)) {
                            $data['expiringFranchise'][] = [
                                'type' => 'case_expiry',
                                'title' => '1-Year Renewal Alert',
                                'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') is due for renewal under a year (' . $expDt->format('M d, Y') . ').',
                                'url' => route('decision-management.index')
                            ];
                        }
                    }
                }

                // 2. Global Notifications for Maintenance Today
                if ($user->hasAccessTo('maintenance.*')) {
                    $todayMaintenance = \Illuminate\Support\Facades\DB::table('maintenance')
                        ->join('units', 'maintenance.unit_id', '=', 'units.id')
                        ->whereNull('maintenance.deleted_at')
                        ->where('maintenance.date_started', date('Y-m-d'))
                        ->where('maintenance.status', '!=', 'completed')
                        ->select('maintenance.id', 'units.plate_number', 'maintenance.maintenance_type')
                        ->get();

                    foreach($todayMaintenance as $tm) {
                        $data['maintNotifs'][] = [
                            'type' => 'maintenance_today',
                            'title' => 'Maintenance Today',
                            'message' => "Unit {$tm->plate_number} is scheduled for " . ucfirst($tm->maintenance_type) . " maintenance today.",
                            'url' => route('maintenance.index', ['search' => $tm->plate_number])
                        ];
                    }
                }
                
                // 2.5. Global Notifications for Odometer-based Maintenance Due (>= 5000 KM)
                if ($user->hasAccessTo('maintenance.*')) {
                    $dueUnits = \Illuminate\Support\Facades\DB::table('units')
                        ->whereNull('deleted_at')
                        ->whereRaw('(current_gps_odo - last_service_odo_gps) >= 5000')
                        ->where('status', '!=', 'maintenance')
                        ->get();

                    foreach ($dueUnits as $du) {
                        $km_since = (float)($du->current_gps_odo - $du->last_service_odo_gps);
                        $data['odoMaintNotifs'][] = [
                            'type' => 'odo_maint_due',
                            'title' => '⚠ Service Required: ' . $du->plate_number,
                            'message' => "Unit {$du->plate_number} has reached " . number_format($km_since, 0) . " KM since last service. Maintenance is now REQUIRED.",
                            'url' => route('units.index', ['search' => $du->plate_number]),
                            'time' => 'CRITICAL',
                            'timestamp' => now()
                        ];
                    }
                }

                // 3. Global Notifications for Low Stock Spare Parts (<= 5)
                if ($user->hasAccessTo('maintenance.*') || $user->hasAccessTo('spare-parts.*')) {
                    $lowStockParts = \Illuminate\Support\Facades\DB::table('spare_parts')
                        ->where('stock_quantity', '<=', 5)
                        ->get();
                    
                    foreach ($lowStockParts as $p) {
                        $qty = (int)($p->stock_quantity ?? 0);
                        $statusText = $qty === 0 ? 'OUT OF STOCK' : 'Low Stock';
                        $timeLabel = $qty === 0 ? 'REORDER NOW' : 'Critical';
                        
                        $data['stockNotifs'][] = [
                            'type' => 'low_stock',
                            'title' => '⚠ ' . $statusText . ': ' . $p->name,
                            'message' => "Only {$qty} items remaining in inventory. Please reorder from " . ($p->supplier ?? 'supplier') . ".",
                            'url' => route('maintenance.index', ['open_inventory' => 1]),
                            'time' => $timeLabel,
                            'timestamp' => \Carbon\Carbon::parse($p->updated_at ?? now())
                        ];
                    }
                }
                
                // 4. Global Notifications for Driver License Expiry
                if ($user->hasAccessTo('driver-management.*')) {
                    $expiringLicenses = \Illuminate\Support\Facades\DB::table('drivers')
                        ->whereNull('deleted_at')
                        ->where('license_expiry', '<=', Carbon::now()->addDays(30)->toDateString())
                        ->get();
                    
                    foreach ($expiringLicenses as $l) {
                        $expDt = Carbon::parse($l->license_expiry);
                        $isExpired = $expDt->isPast();
                        $driverName = $l->first_name . ' ' . $l->last_name;
                        
                        $data['licenseNotifs'][] = [
                            'id' => 'lic_exp_' . $l->id,
                            'type' => 'license_expiry',
                            'title' => $isExpired ? '🚫 Expired License: ' . $driverName : '⚠️ License Renewal: ' . $driverName,
                            'message' => "{$driverName}'s license " . ($isExpired ? 'expired on ' : 'expires on ') . $expDt->format('M d, Y') . ". Please update the record.",
                            'url' => route('driver-management.index') . '?edit_driver=' . $l->id,
                            'time' => $isExpired ? 'ACTION REQUIRED' : 'Upcoming',
                            'timestamp' => $expDt
                        ];
                    }
                }

                $view->with('expiringFranchise', $data['expiringFranchise']);
                $view->with('maintNotifs', $data['maintNotifs']);
                $view->with('odoMaintNotifs', $data['odoMaintNotifs']);
                $view->with('stockNotifs', $data['stockNotifs']);
                $view->with('licenseNotifs', $data['licenseNotifs']);

            } catch (\Exception $e) {
                // If DB is missing during initial setup, silently ignore
            }
        });
    }
}
