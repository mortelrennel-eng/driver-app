<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentPartsEstimate extends Model
{
    protected $fillable = ['driver_behavior_id', 'spare_part_id', 'custom_part_name', 'quantity', 'unit_price', 'total_price', 'is_charged_to_driver'];

    public function incident()
    {
        return $this->belongsTo(DriverBehavior::class, 'driver_behavior_id');
    }

    public function part()
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }
}
