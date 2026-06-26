<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TrackChanges;

class RescueRequest extends Model
{
    use SoftDeletes, TrackChanges;

    protected $table = 'rescue_requests';

    protected $fillable = [
        'driver_id',
        'unit_id',
        'type',
        'latitude',
        'longitude',
        'status',
        'notes',
        'photo_path',
        'acknowledged_by',
        'acknowledged_at',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
