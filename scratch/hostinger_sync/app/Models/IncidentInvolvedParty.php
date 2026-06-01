<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentInvolvedParty extends Model
{
    protected $fillable = ['driver_behavior_id', 'name', 'vehicle_type', 'plate_number'];

    public function incident()
    {
        return $this->belongsTo(DriverBehavior::class, 'driver_behavior_id');
    }
}
