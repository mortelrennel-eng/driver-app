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

        // Global Notifications for Franchise Expirations
        // Global Notifications for Franchise Expirations and Maintenance
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (!auth()->check()) {
                return;
            }

            try {
                $user = auth()->user();
                
                // 1. Global Notifications for Franchise Expirations
                $expiringFranchise = [];
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
                            $expiringFranchise[] = [
                                'type' => 'case_expiry',
                                'title' => 'Expired Franchise Alert',
                                'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') has already expired on ' . $expDt->format('M d, Y') . '.',
                                'url' => route('decision-management.index')
                            ];
                        } elseif ($expDt->isBetween($today, $nextYear)) {
                            $expiringFranchise[] = [
                                'type' => 'case_expiry',
                                'title' => '1-Year Renewal Alert',
                                'message' => 'Case No. ' . $c->case_no . ' (' . $c->applicant_name . ') is due for renewal under a year (' . $expDt->format('M d, Y') . ').',
                                'url' => route('decision-management.index')
                            ];
                        }
                    }
                }
                $view->with('expiringFranchise', $expiringFranchise);

                // 2. Global Notifications for Maintenance Today
                $maintNotifs = [];
                if ($user->hasAccessTo('maintenance.*')) {
                    $todayMaintenance = \Illuminate\Support\Facades\DB::table('maintenance')
                        ->join('units', 'maintenance.unit_id', '=', 'units.id')
                        ->whereNull('maintenance.deleted_at')
                        ->where('maintenance.date_started', date('Y-m-d'))
                        ->where('maintenance.status', '!=', 'completed')
                        ->select('maintenance.id', 'units.plate_number', 'maintenance.maintenance_type')
                        ->get();

                    foreach($todayMaintenance as $tm) {
                        $maintNotifs[] = [
                            'type' => 'maintenance_today',
                            'title' => 'Maintenance Today',
                            'message' => "Unit {$tm->plate_number} is scheduled for " . ucfirst($tm->maintenance_type) . " maintenance today.",
                            'url' => route('maintenance.index', ['search' => $tm->plate_number])
                        ];
                    }
                }
                $view->with('maintNotifs', $maintNotifs);

                // 3. Global Notifications for Low Stock Spare Parts (<= 5)
                $stockNotifs = [];
                if ($user->hasAccessTo('maintenance.*') || $user->hasAccessTo('spare-parts.*')) {
                    $lowStockParts = \Illuminate\Support\Facades\DB::table('spare_parts')
                        ->where('stock_quantity', '<=', 5)
                        ->get();
                    
                    foreach ($lowStockParts as $p) {
                        $qty = (int)($p->stock_quantity ?? 0);
                        $statusText = $qty === 0 ? 'OUT OF STOCK' : 'Low Stock';
                        $timeLabel = $qty === 0 ? 'REORDER NOW' : 'Critical';
                        
                        $stockNotifs[] = [
                            'type' => 'low_stock',
                            'title' => '⚠ ' . $statusText . ': ' . $p->name,
                            'message' => "Only {$qty} items remaining in inventory. Please reorder from " . ($p->supplier ?? 'supplier') . ".",
                            'url' => route('maintenance.index', ['open_inventory' => 1]),
                            'time' => $timeLabel,
                            'timestamp' => \Carbon\Carbon::parse($p->updated_at ?? now())
                        ];
                    }
                }
                $view->with('stockNotifs', $stockNotifs);
                
                // 4. Global Notifications for Driver License Expiry
                $licenseNotifs = [];
                if ($user->hasAccessTo('driver-management.*')) {
                    $expiringLicenses = \Illuminate\Support\Facades\DB::table('drivers')
                        ->whereNull('deleted_at')
                        ->where('license_expiry', '<=', Carbon::now()->addDays(30)->toDateString())
                        ->get();
                    
                    foreach ($expiringLicenses as $l) {
                        $expDt = Carbon::parse($l->license_expiry);
                        $isExpired = $expDt->isPast();
                        $driverName = $l->first_name . ' ' . $l->last_name;
                        
                        $licenseNotifs[] = [
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
                $view->with('licenseNotifs', $licenseNotifs);

            } catch (\Exception $e) {
                // If DB is missing during initial setup, silently ignore
            }
        });
    }
}
