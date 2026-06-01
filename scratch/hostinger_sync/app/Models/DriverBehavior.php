<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DriverBehavior extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'driver_behavior';
    public $timestamps = false; // Using custom timestamp columns mostly

    protected $fillable = [
        'unit_id', 'driver_id', 'incident_type', 'sub_classification',
        'traffic_fine_amount', 'cause_of_incident', 'severity', 'description', 
        'third_party_name', 'third_party_vehicle', 'own_unit_damage_cost', 
        'third_party_damage_cost', 'is_driver_fault', 'total_charge_to_driver', 
        'total_paid', 'remaining_balance', 'charge_status', 'latitude', 'longitude', 
        'timestamp', 'incident_date', 'video_url', 'incentive_released_at',
        'missing_days_reported', 'stolen_driver_detail_name', 'stolen_driver_detail_contact', 'stolen_driver_license_no',
    ];

    public function involvedParties()
    {
        return $this->hasMany(IncidentInvolvedParty::class, 'driver_behavior_id');
    }

    public function partsEstimates()
    {
        return $this->hasMany(IncidentPartsEstimate::class, 'driver_behavior_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public const VIOLATION_TYPES = [
        'Short Boundary', 
        'Late Remittance', 
        'Traffic Violation', 
        'Absent / No Show', 
        'Passenger Complaint',
        'Vehicle Damage',
        'Low Fuel'
    ];

    /**
     * Logic: What constitutes a "Violation" that blocks incentives/ratings?
     * 1. Any At-Fault Incident (is_driver_fault = 1)
     * 2. Any Specific Administrative Violation (Short Boundary, Late Remittance, etc.)
     * 3. EXCLUDES: Unit Breakdowns (if not at fault), General Inquiries, etc.
     */
    public function isViolation(): bool
    {
        if ($this->is_driver_fault) return true;
        return in_array($this->incident_type, self::VIOLATION_TYPES);
    }

    public function scopeViolations($query)
    {
        return $query->where(function($q) {
            $q->where('is_driver_fault', 1)
              ->orWhereIn('incident_type', self::VIOLATION_TYPES);
        });
    }
}
