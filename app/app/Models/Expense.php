<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use TrackChanges, SoftDeletes;
    protected $table = 'expenses';

    protected static function booted()
    {
        $clearCache = function () {
            \Illuminate\Support\Facades\Cache::forget('web_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_7');
            \Illuminate\Support\Facades\Cache::forget('api_dashboard_stats_30');
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    protected $fillable = [
        'category',
        'description',
        'vendor_name',
        'amount',
        'payment_method',
        'date',
        'receipt_path',
        'recorded_by',
        'notes',
        'reference_number',
        'unit_id',
        'spare_part_id',
        'franchise_case_id',
        'quantity',
        'unit_price',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'quantity' => 'integer',
        'unit_price' => 'float',
        'date' => 'date',
    ];

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class, 'spare_part_id');
    }
}
