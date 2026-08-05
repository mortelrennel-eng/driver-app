<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentClassification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'incident_classifications';

    protected $fillable = [
        'name',
        'default_severity',
        'color',
        'icon',
        'behavior_mode',      // narrative | complaint | traffic | damage | security
        'sub_options',        // JSON array of sub-classification options
        'auto_ban_trigger',   // If true, certain sub_classifications trigger a ban
        'ban_trigger_value',  // Which sub_classification value triggers auto-ban
        'show_not_at_fault',  // If true, shows "Not at Fault" badge when is_driver_fault is false
    ];

    protected $casts = [
        'sub_options'       => 'array',
        'auto_ban_trigger'  => 'boolean',
        'show_not_at_fault' => 'boolean',
    ];

    /**
     * Returns the default sub-options for each behavior mode
     * used when seeding or when sub_options is null.
     */
    public static function getDefaultSubOptions(string $mode): array
    {
        return match($mode) {
            'complaint' => [
                'Contracting',
                'Discourtesy / Rudeness',
                'Overcharging',
                'Refusal of Service',
                'Unsafe Driving',
                'Improper Conduct',
                'Not Following Route',
                'Other Complaint',
            ],
            'traffic' => [
                'No Valid OR/CR',
                'Illegal Parking',
                'Reckless Driving',
                'Beating Red Light',
                'Overspeeding',
                'No Seatbelt',
                'Use of Mobile Phone While Driving',
                'Obstruction',
                'Other Traffic Violation',
            ],
            default => [],
        };
    }
}
