<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodingRecord extends Model
{
    use TrackChanges, SoftDeletes;
    
    protected $table = 'coding_records';

    protected $fillable = [
        'unit_id',
        'date',
        'cost',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cost' => 'float',
        'date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
