<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boundary extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'boundaries';

    protected $fillable = [
        'unit_id',
        'driver_id',
        'expected_driver_id',
        'date',
        'boundary_amount',
        'actual_boundary',
        'shortage',
        'excess',
        'status',
        'notes',
        'recorded_by',
        'is_extra_driver',
        'vehicle_damaged',
        'has_incentive',
    ];

    protected $casts = [
        'boundary_amount' => 'float',
        'date' => 'date',
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
