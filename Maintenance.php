<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifiedBrowser extends Model
{
    use HasFactory;

    protected $table = 'user_verified_browsers';

    protected $fillable = [
        'user_id',
        'browser_token',
        'ip_address',
        'user_agent',
        'verified_at',
        'last_active_at',
    ];

    protected $casts = [
        'verified_at'    => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
