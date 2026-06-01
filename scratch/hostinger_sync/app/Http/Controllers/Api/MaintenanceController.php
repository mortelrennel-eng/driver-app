<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    public function index()
    {
        $records = DB::table('maintenance as m')
            ->leftJoin('units as u', 'm.unit_id', '=', 'u.id')
            ->whereNull('m.deleted_at')
            ->select('m.*', 'u.plate_number', 'u.unit_number')
            ->orderByDesc('m.date_started')
            ->get();
        return response()->json(['success' => true, 'data' => $records]);
    }

    public function show($id)
    {
        $record = DB::table('maintenance')->where('id', $id)->first();
        if (!$record) return response()->json(['success' => false], 404);
        $parts = DB::table('maintenance_parts')->where('maintenance_id', $id)->get();
        return response()->json(['success' => true, 'data' => $record, 'parts' => $parts]);
    }

    public function getSpareParts(Request $request)
    {
        $parts = DB::table('spare_parts')
            ->where('name', 'LIKE', "%{$request->search}%")
            ->limit(50)
            ->get();
        return response()->json(['success' => true, 'data' => $parts]);
    }

    public function store(Request $request)
    {
        $id = DB::table('maintenance')->insertGetId([
            'unit_id' => $request->unit_id,
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'status' => 'pending',
            'cost' => $request->cost,
            'date_started' => $request->date_started,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json(['success' => true, 'id' => $id]);
    }
}
