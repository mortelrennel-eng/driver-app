<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'group'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function verifyPassword($password)
    {
        $storedHashed = self::get('archive_deletion_password');
        if (!$storedHashed) return false;
        return \Illuminate\Support\Facades\Hash::check($password, $storedHashed);
    }
}
