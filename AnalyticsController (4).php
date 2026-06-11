<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    /**
     * Display a listing of the units.
     */
    public function index()
    {
        $units = Unit::all()->map(function($unit) {
            return [
                'id' => $unit->id,
                'plate_number' => $unit->plate_number,
                'status' => $unit->status,
                'boundary_rate' => $unit->boundary_rate,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    /**
     * Display the specified unit.
     */
    public function show($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit,
        ]);
    }
}
