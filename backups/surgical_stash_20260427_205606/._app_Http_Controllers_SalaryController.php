<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = (int)date('m');
        $currentYear = (int)date('Y');
        $search = $request->input('search', '');

        $query = DB::table('salaries as s')
            ->leftJoin('users as u', 's.employee_id', '=', 'u.id')
            ->whereNull('u.deleted_at')
            ->select(
                's.*',
                'u.full_name as employee_name',
                'u.email',
                's.employee_type as position',
                's.total_salary as total_pay'
            )
            ->where('s.month', $currentMonth)
            ->where('s.year', $currentYear);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.full_name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('s.employee_type', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        $salaries = $query->orderBy('u.full_name')->get();

        // Fetch expenses for the current month
        $expense_records = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->orderByDesc('date')
            ->get();

        // Calculate income from boundaries for net profit
        $total_income = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('actual_boundary') ?? 0;

        // Calculate totals/summary
        $total_salaries = $salaries->sum('total_pay');
        $total_expenses = $expense_records->sum('amount');
        $total_employees = DB::table('users')->whereNull('deleted_at')->where('is_active', 1)->count();
        $net_profit = $total_income - ($total_salaries + $total_expenses);

        $summary = [
            'total_employees' => $total_employees,
            'total_salaries' => $total_salaries,
            'total_expenses' => $total_expenses,
            'net_profit' => $net_profit,
            'avg_salary' => $total_employees > 0 ? $total_salaries / $total_employees : 0,
            'avg_expense' => $total_employees > 0 ? $total_expenses / $total_employees : 0,
        ];

        // Get employees for dropdown
        $employees = DB::table('users')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->whereIn('role', ['admin', 'staff'])
            ->select('id', 'full_name', 'role')
            ->orderBy('full_name')
            ->get();

        return view('salary.index', compact('salaries', 'expense_records', 'summary', 'search', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'employee_type' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'holiday_pay' => 'nullable|numeric|min:0',
            'night_differential' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'pay_date' => 'required|date',
        ]);

        $total_salary = $data['basic_salary'] + $data['overtime_pay'] + $data['holiday_pay'] + $data['night_differential'] + $data['allowance'];

        DB::table('salaries')->insert([
            'employee_id' => $data['employee_id'],
            'employee_type' => $data['employee_type'],
            'basic_salary' => $data['basic_salary'],
            'overtime_pay' => $data['overtime_pay'],
            'holiday_pay' => $data['holiday_pay'],
            'night_differential' => $data['night_differential'],
            'allowance' => $data['allowance'],
            'total_salary' => $total_salary,
            'month' => $data['month'],
            'year' => $data['year'],
            'pay_date' => $data['pay_date'],
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('salary.index')->with('success', 'Salary record added successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'employee_type' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'holiday_pay' => 'nullable|numeric|min:0',
            'night_differential' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'pay_date' => 'required|date',
        ]);

        $total_salary = $data['basic_salary'] + $data['overtime_pay'] + $data['holiday_pay'] + $data['night_differential'] + $data['allowance'];

        DB::table('salaries')->where('id', $id)->update([
            'employee_type' => $data['employee_type'],
            'basic_salary' => $data['basic_salary'],
            'overtime_pay' => $data['overtime_pay'],
            'holiday_pay' => $data['holiday_pay'],
            'night_differential' => $data['night_differential'],
            'allowance' => $data['allowance'],
            'total_salary' => $total_salary,
            'pay_date' => $data['pay_date'],
            'updated_at' => now(),
        ]);

        return redirect()->route('salary.index')->with('success', 'Salary record updated successfully');
    }

    public function destroy($id)
    {
        DB::table('salaries')->where('id', $id)->delete();
        return redirect()->route('salary.index')->with('success', 'Salary record deleted successfully');
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $records = DB::table('salaries as s')
            ->leftJoin('users as u', 's.employee_id', '=', 'u.id')
            ->select('s.*', 'u.full_name')
            ->where('s.month', $month)
            ->where('s.year', $year)
            ->orderBy('u.full_name')
            ->get();

        $totals = [
            'total_basic' => $records->sum('basic_salary'),
            'total_overtime' => $records->sum('overtime_pay'),
            'total_holiday' => $records->sum('holiday_pay'),
            'total_night' => $records->sum('night_differential'),
            'total_allowance' => $records->sum('allowance'),
            'total_gross' => $records->sum('total_salary'),
        ];

        return view('salary.report', compact('records', 'month', 'year', 'totals'));
    }
}
