<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'maintenance';
 
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
