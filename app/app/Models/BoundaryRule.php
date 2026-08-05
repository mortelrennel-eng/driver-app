<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoundaryRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'regular_rate',
        'sat_discount',
        'sun_discount',
        'coding_rate',
        'coding_is_fixed', // true if coding rate is a fixed amount (like Tier 3), false if it's 50%
    ];

    protected $casts = [
        'coding_is_fixed' => 'boolean',
        'regular_rate' => 'decimal:2',
        'sat_discount' => 'decimal:2',
        'sun_discount' => 'decimal:2',
        'coding_rate' => 'decimal:2',
    ];
}
