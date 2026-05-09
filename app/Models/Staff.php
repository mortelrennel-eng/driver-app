<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'role',
        'phone',
        'contact_person',
        'emergency_phone',
        'address',
        'status',
    ];

    /**
     * Get the staff's name capitalized.
     */
    public function getNameAttribute($value)
    {
        return ucwords(strtolower($value));
    }
}
