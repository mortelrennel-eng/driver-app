<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoundaryController extends Controller
{
    public function index()
    {
        $boundaries = DB::table('boundaries as b')
            ->leftJoin('units as u', 'b.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'b.driver_id', '=', 'd.id')
            ->whereNull('b.deleted_at')
            ->select('b.*', 'u.plate_number', 'd.first_name', 'd.last_name')
            ->orderByDesc('b.date')
            ->get();
        return response()->json(['success' => true, 'data' => $boundaries]);
    }
}
