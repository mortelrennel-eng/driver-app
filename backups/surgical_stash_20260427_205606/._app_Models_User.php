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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_verified' => 'boolean',
        'last_login'  => 'datetime',
    ];

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
