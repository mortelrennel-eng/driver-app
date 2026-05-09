<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'full_name',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'role',
        'password',
        'password_hash',
        'is_active',
        'is_verified',
        'phone',
        'phone_number',
        'address',
        'github_id',
        'github_token',
        'github_refresh_token',
        'last_login',
        'profile_image',
        'otp_code',
        'otp_expires_at',
        'verified_at',
        'approval_status',
        'approved_by',
        'approved_at',
        'allowed_pages',
        'must_change_password',
        'temp_password',
        'is_disabled',
        'disable_reason',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'is_verified'          => 'boolean',
        'must_change_password' => 'boolean',
        'last_login'           => 'datetime',
        'approved_at'          => 'datetime',
        'allowed_pages'        => 'array',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Local request cache to speed up multiple permission checks in one page load.
     */
    protected static array $permissionCache = [];

    /**
     * Check if the user has access to a specific route pattern.
     */
    public function hasAccessTo(string $pattern): bool
    {
        // 1. Super admins bypass all checks
        if ($this->role === 'super_admin') {
            return true;
        }

        // 2. Always allowed routes (never restricted)
        $alwaysAllowed = [
            'login',
            'logout',
            'register',
            'my-account',
            'my-account.*',
            'notifications.dismiss',
        ];

        foreach ($alwaysAllowed as $p) {
            if (\Illuminate\Support\Str::is($p, $pattern) || $pattern === $p) {
                return true;
            }
        }

        // 3. Get allowed pages
        // Handle casted array or raw JSON string
        $pages = $this->allowed_pages;
        if (is_string($pages)) {
            $pages = json_decode($pages, true);
        }

        // Default to NO access if no restrictions are defined (null or empty array)
        if ($pages === null || !is_array($pages)) {
            return false;
        }

        // 5. Check against the user's allowed_pages list
        foreach ($pages as $allowed) {
            // Check direct match or wildcard match
            if ($allowed === $pattern || \Illuminate\Support\Str::is($allowed, $pattern)) {
                return true;
            }
        }

        // 6. Access denied
        return false;
    }

    /**
     * Get the user's full name capitalized.
     */
    public function getFullNameAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    /**
     * Get the user's first name capitalized.
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst(strtolower($value));
    }

    /**
     * Get the user's middle name capitalized.
     */
    public function getMiddleNameAttribute($value)
    {
        return ucfirst(strtolower($value));
    }

    /**
     * Get the user's last name capitalized.
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst(strtolower($value));
    }



    public function driver()
    {
        return $this->hasOne(Driver::class, 'user_id');
    }

    public function verifiedBrowsers()
    {
        return $this->hasMany(VerifiedBrowser::class, 'user_id');
    }
}
