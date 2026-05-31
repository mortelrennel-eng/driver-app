<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'maintenance';

    protected static function booted()
    {
        $clearCache = function () {
            \Illuminate\Support\Facades\Cache::forget('web_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_7');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_30');
        };

        static::saved($clearCache);
        static::deleted($clearCache);

        // Auto FCM Push on creation — notify ONLY the assigned driver of this unit
        static::created(function ($maintenance) {
            try {
                $unit = $maintenance->unit;
                $plate = $unit ? $unit->plate_number : 'Unknown Unit';
                $type = ucfirst($maintenance->maintenance_type);

                $title = "🔧 Maintenance Scheduled";
                $body  = "Your unit ({$plate}) is scheduled for {$type} maintenance.";

                // Find the driver(s) assigned to this unit and notify only them
                $driverIds = [];
                if ($unit) {
                    if (!empty($unit->driver_id))           $driverIds[] = $unit->driver_id;
                    if (!empty($unit->secondary_driver_id)) $driverIds[] = $unit->secondary_driver_id;
                }

                // If maintenance record has a direct driver_id, include that too
                if (!empty($maintenance->driver_id)) {
                    $driverIds[] = $maintenance->driver_id;
                }

                $driverIds = array_unique(array_filter($driverIds));

                if (!empty($driverIds)) {
                    // Get user_ids from drivers table
                    $userIds = \Illuminate\Support\Facades\DB::table('drivers')
                        ->whereIn('id', $driverIds)
                        ->whereNotNull('user_id')
                        ->pluck('user_id');

                    // Get FCM tokens for those users
                    $tokens = \Illuminate\Support\Facades\DB::table('users')
                        ->whereIn('id', $userIds)
                        ->whereNotNull('fcm_token')
                        ->where('fcm_token', '!=', '')
                        ->pluck('fcm_token')
                        ->unique();

                    foreach ($tokens as $token) {
                        \App\Services\FirebasePushService::sendPush($title, $body, $token, 'maintenance_today');
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('FCM Auto-Push Maintenance Error: ' . $e->getMessage());
            }
        });

    }
 
    protected $fillable = [
        'unit_id',
        'driver_id',
        'maintenance_type',
        'description',
        'labor_cost',
        'odometer_reading',
        'date_started',
        'date_completed',
        'status',
        'mechanic_name',
        'parts_list',
        'cost',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cost' => 'float',
        'date_started' => 'date',
        'date_completed' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
