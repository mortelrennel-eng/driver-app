<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FranchiseCase extends Model
{
    use SoftDeletes;
    protected $table = 'franchise_cases';

    protected $fillable = [
        'case_no',
        'applicant_name',
        'unit_id',
        'status',
        'filing_date',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'expiry_date' => 'date',
    ];
}
