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

        // Auto FCM Push on creation
        static::created(function ($maintenance) {
            try {
                $unit = $maintenance->unit;
                $plate = $unit ? $unit->plate_number : 'Unknown Unit';
                $type = ucfirst($maintenance->maintenance_type);
                
                $title = "🔧 New Maintenance Scheduled";
                $body = "Unit {$plate} is scheduled for {$type} maintenance today.";

                // Get all registered FCM tokens in database
                $tokens = \Illuminate\Support\Facades\DB::table('users')
                    ->whereNotNull('fcm_token')
                    ->where('fcm_token', '!=', '')
                    ->pluck('fcm_token')
                    ->unique();

                foreach ($tokens as $token) {
                    \App\Services\FirebasePushService::sendPush($title, $body, $token, 'maintenance_today');
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
