<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TrackChanges
{
    /**
     * Boot the trait and register Eloquent events.
     *
     * @return void
     */
    public static function bootTrackChanges()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (!$model->created_by) {
                    $model->created_by = Auth::id();
                }
                if (!$model->updated_by) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the user who created the record.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the record.
     */
    public function editor()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
