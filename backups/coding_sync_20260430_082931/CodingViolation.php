<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodingViolation extends Model
{
    protected $table = 'coding_violations';

    protected $fillable = [
        'unit_id',
        'violation_type',
        'location_name',
        'latitude',
        'longitude',
        'violation_time',
    ];

    protected $casts = [
        'violation_time' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
