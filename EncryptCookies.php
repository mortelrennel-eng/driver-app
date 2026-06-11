<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    protected $table = 'login_audit';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'ip_address',
        'user_agent',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Log an audit event.
     */
    public static function log(string $action, $user = null, string $notes = null): void
    {
        static::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->full_name ?? $user?->name,
            'user_email' => $user?->email,
            'user_role'  => $user?->role,
            'action'     => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes'      => $notes,
            'created_at' => now(),
        ]);
    }
}
