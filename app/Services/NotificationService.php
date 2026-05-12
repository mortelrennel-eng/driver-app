<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Get all active notifications across the entire system.
     * Logic synced with AppServiceProvider View Composer.
     */
    public function getGlobalNotifications()
    {
        return Cache::remember('global_notifications', 30, function() {
            $headerNotifications = [];
            $now = Carbon::now('Asia/Manila');
            $today = $now->toDateString();

            try {
                // ─── 1. RUN AUTOMATED SCANNERS & SYNC TO system_alerts ───

                // A. Flagged "At Risk" (Highest Priority)
                $flagged = DB::table('units')->whereNull('deleted_at')->where('status', 'at_risk')->get();
                foreach($flagged as $f) {
                    $type = 'at_risk';
                    $title = '🚨 Flagged: ' . $f->plate_number;
                    $message = 'This unit is currently flagged as At Risk.';
                    
                    $exists = DB::table('system_alerts')
                        ->where('type', $type)
                        ->where('title', $title)
                        ->where('is_resolved', false)
                        ->exists();
                    if (!$exists) {
                        DB::table('system_alerts')->insert([
                            'type' => $type,
                            'title' => $title,
                            'message' => $message,
                            'is_resolved' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Auto-resolve at_risk alerts if status is no longer at_risk
                $activeAtRiskAlerts = DB::table('system_alerts')->where('type', 'at_risk')->where('is_resolved', false)->get();
                foreach ($activeAtRiskAlerts as $ara) {
                    $plateStr = str_replace("🚨 Flagged: ", "", $ara->title);
                    $u = DB::table('units')->where('plate_number', $plateStr)->whereNull('deleted_at')->first();
                    if (!$u || $u->status !== 'at_risk') {
                        DB::table('system_alerts')->where('id', $ara->id)->update(['is_resolved' => true, 'updated_at' => now()]);
                    }
                }

                // B. Franchise Case Renewals / Expirations
                $cases = DB::table('franchise_cases')->whereNull('deleted_at')->whereNotNull('expiry_date')->get();
                foreach ($cases as $c) {
                    $expDt = Carbon::parse($c->expiry_date);
                    if ($expDt->isPast() || $expDt->isBetween($now, $now->copy()->addYear())) {
                        $isExpired = $expDt->isPast();
                        $type = 'case_expiry';
                        $title = $isExpired ? 'Expired Franchise' : 'Franchise Renewal';
                        $msg = 'Case ' . $c->case_no . ' (' . $c->applicant_name . ') ' . ($isExpired ? 'expired on ' : 'expires on ') . $expDt->format('M d, Y');
                        
                        $exists = DB::table('system_alerts')
                            ->where('type', $type)
                            ->where('title', $title)
                            ->where('message', $msg)
                            ->where('is_resolved', false)
                            ->exists();
                        if (!$exists) {
                            DB::table('system_alerts')->insert([
                                'type' => $type,
                                'title' => $title,
                                'message' => $msg,
                                'is_resolved' => false,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }

                // C. Maintenance Today Scheduled Alert
                $todayMaint = DB::table('maintenance')
                    ->join('units', 'maintenance.unit_id', '=', 'units.id')->whereNull('maintenance.deleted_at')
                    ->where('maintenance.date_started', $today)->where('maintenance.status', '!=', 'completed')
                    ->select('maintenance.id', 'units.plate_number', 'maintenance.maintenance_type')->get();
                foreach($todayMaint as $tm) {
                    $type = 'maintenance_today';
                    $title = 'Maintenance Today';
                    $msg = "Unit {$tm->plate_number} schedule: " . ucfirst($tm->maintenance_type);
                    
                    $exists = DB::table('system_alerts')
                        ->where('type', $type)
                        ->where('title', $title)
                        ->where('message', $msg)
                        ->where('is_resolved', false)
                        ->exists();
                    if (!$exists) {
                        DB::table('system_alerts')->insert([
                            'type' => $type,
                            'title' => $title,
                            'message' => $msg,
                            'is_resolved' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // D. Low Stock Spare Parts (<= 5 items)
                $lowStock = DB::table('spare_parts')->where('stock_quantity', '<=', 5)->get();
                foreach ($lowStock as $p) {
                    $qty = (int)$p->stock_quantity;
                    $type = 'low_stock';
                    $title = ($qty === 0 ? '⚠ OUT OF STOCK: ' : '⚠ Low Stock: ') . $p->name;
                    $msg = "Stock: {$qty} items. Source: " . ($p->supplier ?? 'Unspecified');
                    
                    $exists = DB::table('system_alerts')
                        ->where('type', $type)
                        ->where('title', $title)
                        ->where('is_resolved', false)
                        ->exists();
                    if (!$exists) {
                        DB::table('system_alerts')->insert([
                            'type' => $type,
                            'title' => $title,
                            'message' => $msg,
                            'is_resolved' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Auto-resolve low stock alerts if stock has been replenished
                $activeLowStock = DB::table('system_alerts')->where('type', 'low_stock')->where('is_resolved', false)->get();
                foreach ($activeLowStock as $als) {
                    $partName = str_replace(['⚠ OUT OF STOCK: ', '⚠ Low Stock: '], '', $als->title);
                    $p = DB::table('spare_parts')->where('name', $partName)->first();
                    if (!$p || $p->stock_quantity > 5) {
                        DB::table('system_alerts')->where('id', $als->id)->update(['is_resolved' => true, 'updated_at' => now()]);
                    }
                }

                // E. Driver License Expiry Alert (<= 30 days remaining)
                $expiringDrivers = DB::table('drivers')
                    ->whereNull('deleted_at')
                    ->where('license_expiry', '<=', $now->copy()->addDays(30)->toDateString())
                    ->get();
                foreach ($expiringDrivers as $ed) {
                    $expDt = Carbon::parse($ed->license_expiry);
                    $isExpired = $expDt->isPast();
                    $driverName = trim(($ed->first_name ?? '') . ' ' . ($ed->last_name ?? ''));
                    $type = 'license_expiry';
                    $title = $isExpired ? '🚫 Expired License: ' . $driverName : '⚠️ License Renewal: ' . $driverName;
                    $msg = "{$driverName}'s license " . ($isExpired ? 'expired on ' : 'expires on ') . $expDt->format('M d, Y') . ". Please update the record.";
                    
                    $exists = DB::table('system_alerts')
                        ->where('type', $type)
                        ->where('title', $title)
                        ->where('is_resolved', false)
                        ->exists();
                    if (!$exists) {
                        DB::table('system_alerts')->insert([
                            'type' => $type,
                            'title' => $title,
                            'message' => $msg,
                            'is_resolved' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Auto-resolve driver license alerts upon record update
                $activeLicenseAlerts = DB::table('system_alerts')->where('type', 'license_expiry')->where('is_resolved', false)->get();
                foreach ($activeLicenseAlerts as $ala) {
                    $driverName = str_replace(['🚫 Expired License: ', '⚠️ License Renewal: '], '', $ala->title);
                    $d = DB::table('drivers')
                        ->whereNull('deleted_at')
                        ->whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$driverName])
                        ->first();
                    if (!$d || Carbon::parse($d->license_expiry)->isAfter($now->copy()->addDays(30))) {
                        DB::table('system_alerts')->where('id', $ala->id)->update(['is_resolved' => true, 'updated_at' => now()]);
                    }
                }

                // F. Odometer-based Maintenance Due (>= 5000 KM)
                $dueUnits = DB::table('units')
                    ->whereNull('deleted_at')
                    ->whereRaw('(current_gps_odo - last_service_odo_gps) >= 5000')
                    ->where('status', '!=', 'maintenance')
                    ->get();
                foreach ($dueUnits as $du) {
                    $km_since = (float)($du->current_gps_odo - $du->last_service_odo_gps);
                    $type = 'odo_maint_due';
                    $title = '🔧 Service Due: ' . $du->plate_number;
                    $msg = "Unit {$du->plate_number} has reached " . number_format($km_since, 0) . " KM since last service. Maintenance is now REQUIRED.";
                    
                    $exists = DB::table('system_alerts')
                        ->where('type', $type)
                        ->where('title', $title)
                        ->where('is_resolved', false)
                        ->exists();
                    if (!$exists) {
                        DB::table('system_alerts')->insert([
                            'type' => $type,
                            'title' => $title,
                            'message' => $msg,
                            'is_resolved' => false,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Auto-resolve odometer-based alerts
                $activeOdoAlerts = DB::table('system_alerts')->where('type', 'odo_maint_due')->where('is_resolved', false)->get();
                foreach ($activeOdoAlerts as $aoa) {
                    $plateStr = str_replace('🔧 Service Due: ', '', $aoa->title);
                    $u = DB::table('units')->where('plate_number', $plateStr)->whereNull('deleted_at')->first();
                    if (!$u || ($u->current_gps_odo - $u->last_service_odo_gps) < 5000 || $u->status === 'maintenance') {
                        DB::table('system_alerts')->where('id', $aoa->id)->update(['is_resolved' => true, 'updated_at' => now()]);
                    }
                }


                // ─── 2. RETRIEVE ALL ACTIVE PERSISTED ALERTS FROM DB ───
                // Fetch everything directly from system_alerts which has 100% parity with pushes!
                $dbAlerts = DB::table('system_alerts')
                    ->where('is_resolved', false)
                    ->orderByDesc('created_at')
                    ->limit(40)
                    ->get();
                
                foreach ($dbAlerts as $a) {
                    $url = '#';
                    if ($a->type === 'missing_unit' || $a->type === 'coding_notice' || $a->type === 'at_risk' || $a->type === 'odo_maint_due') {
                        $url = route('units.index') . '?open_flagged=1';
                    } elseif ($a->type === 'case_expiry') {
                        $url = route('decision-management.index');
                    } elseif ($a->type === 'maintenance_today') {
                        $url = route('maintenance.index');
                    } elseif ($a->type === 'low_stock') {
                        $url = route('maintenance.index', ['open_inventory' => 1]);
                    } elseif ($a->type === 'license_expiry') {
                        $url = route('driver-management.index');
                    } else {
                        $url = route('driver-behavior.index');
                    }

                    $headerNotifications[] = [
                        'id' => $a->id,
                        'type' => $a->type,
                        'title' => $a->title,
                        'message' => $a->message,
                        'url' => $url,
                        'time' => Carbon::parse($a->created_at)->diffForHumans(),
                        'timestamp' => Carbon::parse($a->created_at)
                    ];
                }

                // --- SORTING BY TIMELINE ---
                usort($headerNotifications, function($a, $b) {
                    $timeA = isset($a['timestamp']) ? $a['timestamp']->timestamp : 0;
                    $timeB = isset($b['timestamp']) ? $b['timestamp']->timestamp : 0;
                    return $timeB - $timeA;
                });

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('NotificationService Error: ' . $e->getMessage());
            }

            return $headerNotifications;
        });
    }

    /**
     * Send personalized push notifications to drivers whose vehicles are on coding today.
     */
    public function dispatchDailyCodingNotifications()
    {
        $todayName = now()->timezone('Asia/Manila')->format('l');
        
        // Find units with coding today
        $units = \App\Models\Unit::where('coding_day', $todayName)
            ->orWhere(function($q) use ($todayName) {
                $q->whereNull('coding_day')->orWhere('coding_day', '');
            })
            ->get();

        $notifiedCount = 0;

        foreach ($units as $unit) {
            // If coding_day is empty, derive it dynamically
            $actualCodingDay = $unit->coding_day;
            if (empty($actualCodingDay)) {
                $lastDigit = substr(preg_replace('/[^0-9]/', '', $unit->plate_number), -1);
                $days = [1 => 'Monday', 2 => 'Monday', 3 => 'Tuesday', 4 => 'Tuesday', 5 => 'Wednesday', 6 => 'Wednesday', 7 => 'Thursday', 8 => 'Thursday', 9 => 'Friday', 0 => 'Friday'];
                $actualCodingDay = $days[$lastDigit] ?? null;
            }

            if ($actualCodingDay !== $todayName) continue;

            // Notify both primary and secondary drivers
            $driverIds = array_filter([$unit->driver_id, $unit->secondary_driver_id]);
            if (empty($driverIds)) continue;

            $drivers = \App\Models\User::whereIn('id', function($q) use ($driverIds) {
                $q->select('user_id')->from('drivers')->whereIn('id', $driverIds)->whereNotNull('user_id');
            })->whereNotNull('fcm_token')->get();

            foreach ($drivers as $user) {
                $success = \App\Services\FirebasePushService::sendPush(
                    'Coding Alert Today!',
                    "ALERTO: Coding po ang unit niyo ({$unit->plate_number}) ngayong {$todayName}. Mag-ingat po!",
                    $user->fcm_token,
                    'coding'
                );
                if ($success) $notifiedCount++;
            }
        }

        return $notifiedCount;
    }

    /**
     * Get a personalized feed for a specific driver.
     */
    public function getDriverFeed($userId)
    {
        $driver = DB::table('drivers')->where('user_id', $userId)->first();
        if (!$driver) return [];

        $feed = [];

        // 1. Remittances (Boundaries)
        $boundaries = DB::table('boundaries')
            ->where('driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->limit(10)
            ->get();
        
        foreach ($boundaries as $b) {
            $severity = 'info';
            if ($b->status === 'paid') $severity = 'success';
            elseif ($b->status === 'shortage') $severity = 'danger';
            elseif ($b->status === 'excess') $severity = 'warning';

            $feed[] = [
                'id' => 'remit_' . $b->id,
                'type' => 'remittance',
                'title' => 'Remittance Processed',
                'message' => "Boundary remitted: ₱" . number_format($b->actual_boundary, 2) . " for " . Carbon::parse($b->date)->format('M d, Y') . ". Status: " . strtoupper($b->status),
                'timestamp' => $b->created_at,
                'time_display' => Carbon::parse($b->created_at)->diffForHumans(),
                'severity' => $severity,
                'icon' => 'cash-outline'
            ];
        }

        // 2. Charges/Incidents
        $incidents = DB::table('driver_behavior')
            ->where('driver_id', $driver->id)
            ->whereNull('deleted_at')
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get();

        foreach ($incidents as $i) {
            $feed[] = [
                'id' => 'incident_' . $i->id,
                'type' => 'incident',
                'title' => $i->incident_type,
                'message' => $i->description,
                'timestamp' => $i->timestamp,
                'time_display' => Carbon::parse($i->timestamp)->diffForHumans(),
                'severity' => $i->severity === 'high' ? 'danger' : 'warning',
                'icon' => 'alert-circle-outline'
            ];
        }

        // 3. Support Replies
        $supportReplies = DB::table('support_messages')
            ->where('driver_id', $userId)
            ->where('sender_type', '!=', 'driver')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        foreach ($supportReplies as $sr) {
            $feed[] = [
                'id' => 'support_' . $sr->id,
                'type' => 'system',
                'title' => 'Support Message',
                'message' => $sr->message,
                'timestamp' => $sr->created_at,
                'time_display' => Carbon::parse($sr->created_at)->diffForHumans(),
                'severity' => 'info',
                'icon' => 'megaphone-outline'
            ];
        }



        // 5. Incentives
        $incentives = DB::table('driver_incentives')
            ->where('driver_id', $driver->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        foreach ($incentives as $inc) {
            $feed[] = [
                'id' => 'incentive_' . $inc->id,
                'type' => 'notice',
                'title' => 'Incentive Awarded: ' . ucfirst($inc->incentive_type),
                'message' => "Amount: ₱" . number_format($inc->amount, 2) . ". " . $inc->description,
                'timestamp' => $inc->created_at,
                'time_display' => Carbon::parse($inc->created_at)->diffForHumans(),
                'severity' => 'success',
                'icon' => 'sparkles-outline'
            ];
        }

        // 6. Personalized System Alerts (e.g., Coding Reminders targeted to this driver)
        $systemAlerts = DB::table('system_alerts')
            ->where('is_resolved', false)
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        foreach ($systemAlerts as $a) {
             $feed[] = [
                'id' => 'system_' . $a->id,
                'type' => 'system',
                'title' => $a->title,
                'message' => $a->message,
                'timestamp' => $a->created_at,
                'time_display' => Carbon::parse($a->created_at)->diffForHumans(),
                'severity' => 'info',
                'icon' => 'megaphone-outline'
            ];
        }

        // Sort by timestamp
        usort($feed, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return $feed;
    }

    /**
     * Send a push notification to a specific driver.
     */
    public function notifyDriver($driverId, $title, $body, $type = 'notice')
    {
        $driver = DB::table('drivers')->where('id', $driverId)->first();
        if (!$driver || !$driver->user_id) return false;

        $user = DB::table('users')->where('id', $driver->user_id)->first();
        if (!$user || !$user->fcm_token) return false;

        return \App\Services\FirebasePushService::sendPush($title, $body, $user->fcm_token, $type);
    }
}
