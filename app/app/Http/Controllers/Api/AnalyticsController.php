<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // General analytics data
        $stats = [
            'total_income' => DB::table('boundaries')->sum('amount'),
            'total_expenses' => DB::table('office_expenses')->sum('amount'),
            'maintenance_cost' => DB::table('maintenance')->sum('cost'),
        ];
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function analyze(Request $request)
    {
        // Simple AI mock response
        return response()->json([
            'success' => true,
            'analysis' => 'Based on recent data, unit efficiency is at 85%. Recommend focusing on preventive maintenance for units older than 5 years.'
        ]);
    }

    public function profitability()
    {
        // Mock profitability data
        $data = DB::table('units')->select('id', 'plate_number')->get()->map(function($u) {
            return [
                'id' => $u->id,
                'plate_number' => $u->plate_number,
                'roi' => rand(15, 45) . '%',
                'status' => 'Profitable'
            ];
        });
        return response()->json(['success' => true, 'data' => $data]);
    }
}
