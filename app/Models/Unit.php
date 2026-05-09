<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'units';

    protected $fillable = [
        'plate_number',
        'make',
        'model',
        'year',
        'status',
        'boundary_rate',
        'purchase_date',
        'purchase_cost',
        'motor_no',
        'chassis_no',
        'color',
        'unit_type',
        'coding_day',
        'driver_id',
        'secondary_driver_id',
        'current_turn_driver_id',
        'last_swapping_at',
        'shift_deadline_at',
        'gps_link',
        'imei',
        'dashcam_enabled',
        'latitude',
        'longitude',
        'current_location',
        'last_location_update',
    ];

    protected $casts = [
        'gps_enabled' => 'boolean',
        'dashcam_enabled' => 'boolean',
        'purchase_cost' => 'float',
        'boundary_rate' => 'float',
        'year' => 'integer',
    ];

    public function primaryDriver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Alias for primaryDriver() — keeps backward compatibility with any code
     * that calls Unit::with(['driver']).
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function secondaryDriver()
    {
        return $this->belongsTo(Driver::class, 'secondary_driver_id');
    }

    public function boundaries()
    {
        return $this->hasMany(Boundary::class, 'unit_id');
    }

    public function maintenance()
    {
        return $this->hasMany(Maintenance::class, 'unit_id');
    }

    public function codingRecords()
    {
        return $this->hasMany(CodingRecord::class, 'unit_id');
    }

    public function codingViolations()
    {
        return $this->hasMany(CodingViolation::class, 'unit_id');
    }

    protected static function booted()
    {
        static::saving(function ($unit) {
            // Auto-assign boundary rate based on year model ONLY if the rate wasn't also manually changed
            if ($unit->isDirty('year') && !$unit->isDirty('boundary_rate')) {
                $year = (int) $unit->year;
                $rule = \App\Models\BoundaryRule::where('start_year', '<=', $year)
                    ->where('end_year', '>=', $year)
                    ->first();
                
                if ($rule) {
                    $unit->boundary_rate = $rule->regular_rate;
                }
            }
        });

        $clearCache = function () {
            \Illuminate\Support\Facades\Cache::forget('web_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_7');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_30');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }
}
