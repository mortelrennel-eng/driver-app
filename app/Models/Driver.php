<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'drivers';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'nickname',
        'license_number',
        'contact_number',
        'license_expiry',
        'hire_date',
        'daily_boundary_target',
        'address',
        'emergency_contact',
        'emergency_phone',
        'driver_type',
        'driver_status',
        'created_by',
        'updated_by',
    ];

    public function getFullNameAttribute()
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: 'N/A';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
