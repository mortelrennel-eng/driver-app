<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $table = 'system_alerts';

    protected $fillable = [
        'title',
        'message',
        'alert_type',
        'severity',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];
}
