<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoundaryController extends Controller
{
    /**
     * Display a listing of boundary records.
     */
    public function index(Request $request)
    {
        $today = now()->timezone('Asia/Manila')->toDateString();
        $date = $request->get('date', $today);
        
        $query = DB::table('boundaries as b')
            ->whereNull('b.deleted_at')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->select(
                'b.id',
                'b.date',
                'u.plate_number as unitNumber',
                DB::raw("CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')) as driver"),
                'u.year as unit_year',
                'b.boundary_amount as expectedAmount',
                'b.actual_boundary as paidAmount',
                'b.status',
                'b.shortage',
                'b.notes',
                'b.created_at as paymentTime'
            );

        if ($date) {
            $query->whereDate('b.date', $date);
        }

        $records = $query->orderByDesc('b.created_at')->get();

        // Format for mobile
        $formattedRecords = $records->map(function($r) {
            $r->paymentTime = Carbon::parse($r->paymentTime)->format('h:i A');
            $r->unitType = ($r->unit_year && $r->unit_year >= 2023) ? 'New Unit' : 'Old Unit';
            $r->boundaryType = 'Regular (24hrs)'; // Simplified for mobile for now
            // Status mapping to match mobile frontend (Capitalized)
            $r->status = ucfirst($r->status);
            return $r;
        });

        // Summary stats
        $stats = [
            'totalExpected' => $formattedRecords->sum('expectedAmount'),
            'totalCollected' => $formattedRecords->sum('paidAmount'),
            'paid' => $formattedRecords->where('status', 'Paid')->count(),
            'shortage' => $formattedRecords->where('status', 'Shortage')->count(),
            'unpaid' => 0, 
        ];

        return response()->json([
            'success' => true,
            'records' => $formattedRecords,
            'stats' => $stats
        ]);
    }
}
