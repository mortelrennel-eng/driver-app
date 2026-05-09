<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitProfitabilityController extends Controller
{
    public function index(Request $request)
    {
        // Get date range filter
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-t'));
        $unit_filter = $request->input('unit', '');

        // Build WHERE conditions
        $where_conditions = [];
        $params = [];
        $types = "";

        if (!empty($unit_filter)) {
            $where_conditions[] = "u.plate_number = ?";
            $params[] = $unit_filter;
            $types .= "s";
        }
        $where_conditions[] = "u.deleted_at IS NULL";

        $where_clause = "WHERE " . implode(' AND ', $where_conditions);

        // Get all units for dropdown
        $units_dropdown = DB::table('units')->whereNull('deleted_at')->orderBy('plate_number')->get();

        // Get unit profitability data
        $sql = "SELECT 
                u.id,
                u.plate_number,
                COALESCE(u.make, 'Unknown') as make,
                COALESCE(u.model, 'Unknown') as model,
                COALESCE(u.year, 0) as year,
                COALESCE(u.purchase_cost, 0) as purchase_cost,
                COALESCE(u.boundary_rate, 0) as boundary_rate,
                COALESCE(SUM(CASE WHEN b.date BETWEEN ? AND ? THEN b.actual_boundary ELSE 0 END), 0) as total_boundary,
                COALESCE(SUM(CASE WHEN b.date BETWEEN ? AND ? THEN b.boundary_amount ELSE 0 END), 0) as total_target_boundary,
                COALESCE(COUNT(DISTINCT CASE WHEN b.date BETWEEN ? AND ? THEN b.id END), 0) as boundary_days,
                COALESCE(SUM(CASE WHEN m.date_started BETWEEN ? AND ? THEN m.cost ELSE 0 END), 0) as total_maintenance,
                COALESCE(COUNT(DISTINCT CASE WHEN m.date_started BETWEEN ? AND ? THEN m.id END), 0) as maintenance_days,
                COALESCE(SUM(CASE WHEN e.date BETWEEN ? AND ? THEN e.amount ELSE 0 END), 0) as total_expenses,
                COALESCE(COUNT(DISTINCT CASE WHEN e.date BETWEEN ? AND ? THEN e.id END), 0) as expense_days
            FROM units u
            LEFT JOIN boundaries b ON u.id = b.unit_id AND b.deleted_at IS NULL
            LEFT JOIN maintenance m ON u.id = m.unit_id AND m.deleted_at IS NULL
            LEFT JOIN expenses e ON u.id = e.unit_id AND e.deleted_at IS NULL
            $where_clause
            GROUP BY u.id, u.plate_number, u.make, u.model, u.year, u.purchase_cost, u.boundary_rate
            ORDER BY u.plate_number";

        // Build parameters array
        $all_params = array_merge(
            [$date_from, $date_to], // total_boundary (actual)
            [$date_from, $date_to], // total_target_boundary
            [$date_from, $date_to], // boundary days
            [$date_from, $date_to], // maintenance dates
            [$date_from, $date_to], // maintenance days
            [$date_from, $date_to], // expense dates
            [$date_from, $date_to], // expense days
            $params
        );

        $profitability = DB::select($sql, $all_params);

        // Calculate additional metrics
        foreach ($profitability as &$unit) {
            $unit->net_income = $unit->total_boundary - $unit->total_maintenance - $unit->total_expenses;
            $unit->profit_margin = $unit->total_boundary > 0 ? (($unit->net_income / $unit->total_boundary) * 100) : 0;
            $unit->maintenance_cost = $unit->total_maintenance;
            $unit->other_expenses = $unit->total_expenses;
            $unit->roi_percentage = $unit->purchase_cost > 0 ? (($unit->net_income / $unit->purchase_cost) * 100) : 0;
            $unit->payback_period = $unit->total_boundary > 0 ? ($unit->purchase_cost / $unit->total_boundary) : 0;
            $unit->roi_achieved = $unit->purchase_cost > 0 && $unit->net_income >= $unit->purchase_cost ? 1 : 0;
        }

        // Calculate totals / overview
        $overview = [
            'total_boundary' => array_sum(array_column($profitability, 'total_boundary')),
            'total_maintenance' => array_sum(array_column($profitability, 'total_maintenance')),
            'total_expenses' => array_sum(array_column($profitability, 'total_expenses')),
            'net_income' => array_sum(array_column($profitability, 'net_income')),
            'total_units' => count($profitability),
            'avg_margin' => count($profitability) > 0 ? array_sum(array_column($profitability, 'profit_margin')) / count($profitability) : 0,
            'roi_units' => count(array_filter($profitability, function($u) { return $u->roi_achieved; })),
        ];

        $units = $units_dropdown;
        $selected_unit = $unit_filter;

        return view('unit-profitability.index', compact('profitability', 'units', 'overview', 'date_from', 'date_to', 'selected_unit'));
    }
}
