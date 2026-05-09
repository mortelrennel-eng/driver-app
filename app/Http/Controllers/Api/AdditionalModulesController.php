<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ActivityLogController;
use App\Models\Expense;
use App\Models\Driver;
use App\Models\DriverBehavior;
use App\Models\IncidentClassification;
use App\Models\IncidentInvolvedParty;
use App\Models\IncidentPartsEstimate;
use App\Models\FranchiseCase;
use App\Models\Staff;
use App\Models\User;
use App\Services\GeminiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class AdditionalModulesController extends Controller
{
    // ==========================================
    // 📊 1. FRANCHISE (DECISION MANAGEMENT)
    // ==========================================

    public function franchiseIndex(Request $request)
    {
        $search = $request->input('search', '');
        $page = max(1, (int) $request->input('page', 1));
        $limit = (int) $request->input('limit', 15);
        $offset = ($page - 1) * $limit;

        $hasUnitsTable = Schema::hasTable('franchise_case_units');

        $query = DB::table('franchise_cases')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                  ->orWhere('case_no', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                  ->orWhere('type_of_application', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                  ->orWhere('denomination', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        $total = $query->count();
        $casesCollection = $query->orderByDesc('created_at')->offset($offset)->limit($limit)->get();
        
        $cases = collect($casesCollection)->map(function($c) use ($hasUnitsTable) {
            $row = (array)$c;
            if ($hasUnitsTable) {
                $units = DB::table('franchise_case_units')
                    ->where('franchise_case_id', $row['id'] ?? 0)
                    ->get()
                    ->map(fn($u) => (array)$u)
                    ->toArray();
                $row['units'] = $units;
                $row['unit_count'] = count($units);
            } else {
                $row['units'] = [];
                $row['unit_count'] = 0;
            }
            return $row;
        })->toArray();

        $stats = [
            'total_cases' => DB::table('franchise_cases')->whereNull('deleted_at')->count(),
            'expiring_soon' => DB::table('franchise_cases')
                ->whereNull('deleted_at')
                ->whereNotNull('expiry_date')
                ->whereRaw('expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
                ->count(),
            'expired' => DB::table('franchise_cases')
                ->whereNull('deleted_at')
                ->whereNotNull('expiry_date')
                ->whereRaw('expiry_date < CURDATE()')
                ->count(),
            'pending' => DB::table('franchise_cases')->whereNull('deleted_at')->where('status', 'pending')->count(),
            'approved' => DB::table('franchise_cases')->whereNull('deleted_at')->where('status', 'approved')->count(),
            'rejected' => DB::table('franchise_cases')->whereNull('deleted_at')->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $cases,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $total,
                'total_pages' => max(1, (int) ceil($total / $limit)),
            ]
        ]);
    }

    public function franchiseStore(Request $request)
    {
        // Check for duplicate case_no
        $duplicateCase = DB::table('franchise_cases')
            ->where('case_no', $request->case_no)
            ->whereNull('deleted_at')
            ->first();
            
        if ($duplicateCase) {
            return response()->json([
                'success' => false,
                'message' => 'Error: Case No. "' . $request->case_no . '" already exists in the system.'
            ], 422);
        }

        // Validate plate numbers if units exist
        if ($request->has('units') && is_array($request->units)) {
            $incomingPlates = [];
            foreach ($request->units as $u) {
                if (!empty($u['plate_no'])) {
                    $incomingPlates[] = strtoupper(trim($u['plate_no']));
                }
            }
            if (!empty($incomingPlates)) {
                $duplicatePlate = DB::table('franchise_case_units')
                    ->whereIn('plate_no', $incomingPlates)
                    ->join('franchise_cases', 'franchise_case_units.franchise_case_id', '=', 'franchise_cases.id')
                    ->whereNull('franchise_cases.deleted_at')
                    ->first();

                if ($duplicatePlate) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error: Plate No. "' . $duplicatePlate->plate_no . '" is already registered in Case No. "' . $duplicatePlate->case_no . '".'
                    ], 422);
                }
            }
        }

        $request->merge([
            'applicant_name' => trim((string) $request->input('applicant_name', '')),
            'case_no' => trim((string) $request->input('case_no', '')),
            'type_of_application' => trim((string) $request->input('type_of_application', '')),
            'denomination' => trim((string) $request->input('denomination', '')),
        ]);

        $validator = Validator::make($request->all(), [
            'applicant_name' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'type_of_application' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'denomination' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'case_no' => ['required', 'string', 'max:25', 'regex:/^\d+$/'],
            'date_filed' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:date_filed'],
            'units' => ['nullable', 'array'],
        ], [
            'applicant_name.regex' => 'Name of Applicant: letters only. Allowed: space, period (.), comma (,).',
            'type_of_application.regex' => 'Type of Application: letters only. Allowed: space, period (.), comma (,).',
            'denomination.regex' => 'Denomination: letters only. Allowed: space, period (.), comma (,).',
            'case_no.regex' => 'Case No.: numbers only (no spaces).',
            'expiry_date.after_or_equal' => 'Expiry Date must be the same as or after Date Filed.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $id = DB::table('franchise_cases')->insertGetId([
            'applicant_name' => $request->applicant_name,
            'case_no' => $request->case_no,
            'type_of_application' => $request->type_of_application,
            'denomination' => $request->denomination,
            'date_filed' => $request->date_filed,
            'expiry_date' => $request->expiry_date,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->has('units') && Schema::hasTable('franchise_case_units')) {
            foreach ($request->units as $u) {
                if (!empty($u['make']) || !empty($u['motor_no']) || !empty($u['plate_no'])) {
                    DB::table('franchise_case_units')->insert([
                        'franchise_case_id' => $id,
                        'make' => trim((string)($u['make'] ?? '')),
                        'motor_no' => strtoupper(trim((string)($u['motor_no'] ?? ''))),
                        'chasis_no' => strtoupper(trim((string)($u['chasis_no'] ?? ''))),
                        'plate_no' => strtoupper(trim((string)($u['plate_no'] ?? ''))),
                        'year_model' => trim((string)($u['year_model'] ?? '')),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        ActivityLogController::log('Created Franchise Case via Mobile', "Case No: {$request->case_no}\nApplicant: {$request->applicant_name}");

        return response()->json([
            'success' => true,
            'message' => 'Franchise case created successfully.',
            'id' => $id
        ]);
    }

    public function franchiseUpdate(Request $request, $id)
    {
        $case = FranchiseCase::findOrFail($id);

        $duplicateCase = DB::table('franchise_cases')
            ->where('case_no', $request->case_no)
            ->where('id', '!=', $id)
            ->whereNull('deleted_at')
            ->first();
            
        if ($duplicateCase) {
            return response()->json([
                'success' => false,
                'message' => 'Error: Case No. "' . $request->case_no . '" already exists in the system.'
            ], 422);
        }

        $request->merge([
            'applicant_name' => trim((string) $request->input('applicant_name', '')),
            'case_no' => trim((string) $request->input('case_no', '')),
            'type_of_application' => trim((string) $request->input('type_of_application', '')),
            'denomination' => trim((string) $request->input('denomination', '')),
        ]);

        $validator = Validator::make($request->all(), [
            'applicant_name' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'type_of_application' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'denomination' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'case_no' => ['required', 'string', 'max:25', 'regex:/^\d+$/'],
            'date_filed' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:date_filed'],
            'units' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $case->update([
            'applicant_name' => $request->applicant_name,
            'case_no' => $request->case_no,
            'type_of_application' => $request->type_of_application,
            'denomination' => $request->denomination,
            'date_filed' => $request->date_filed,
            'expiry_date' => $request->expiry_date,
        ]);

        if (Schema::hasTable('franchise_case_units')) {
            DB::table('franchise_case_units')->where('franchise_case_id', $id)->delete();
            if ($request->has('units') && is_array($request->units)) {
                foreach ($request->units as $u) {
                    if (!empty($u['make']) || !empty($u['motor_no']) || !empty($u['plate_no'])) {
                        DB::table('franchise_case_units')->insert([
                            'franchise_case_id' => $id,
                            'make' => trim((string)($u['make'] ?? '')),
                            'motor_no' => strtoupper(trim((string)($u['motor_no'] ?? ''))),
                            'chasis_no' => strtoupper(trim((string)($u['chasis_no'] ?? ''))),
                            'plate_no' => strtoupper(trim((string)($u['plate_no'] ?? ''))),
                            'year_model' => trim((string)($u['year_model'] ?? '')),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        ActivityLogController::log('Updated Franchise Case via Mobile', "Case No: {$request->case_no}\nApplicant: {$request->applicant_name}");

        return response()->json([
            'success' => true,
            'message' => 'Franchise case updated successfully.'
        ]);
    }

    public function franchiseDestroy($id)
    {
        $case = FranchiseCase::findOrFail($id);
        $caseNo = $case->case_no;
        
        if (Schema::hasTable('franchise_case_units')) {
            DB::table('franchise_case_units')->where('franchise_case_id', $id)->delete();
        }
        
        $case->delete();
        ActivityLogController::log('Archived Franchise Case via Mobile', "Case No: {$caseNo} moved to archive.");

        return response()->json([
            'success' => true,
            'message' => 'Case archived successfully'
        ]);
    }

    public function franchiseApprove($id)
    {
        $case = FranchiseCase::findOrFail($id);
        $case->update(['status' => 'approved']);

        ActivityLogController::log('Approved Franchise Case via Mobile', "Case No: {$case->case_no} approved.");

        return response()->json([
            'success' => true,
            'message' => 'Case approved successfully'
        ]);
    }

    public function franchiseReject($id)
    {
        $case = FranchiseCase::findOrFail($id);
        $case->update(['status' => 'rejected']);

        ActivityLogController::log('Rejected Franchise Case via Mobile', "Case No: {$case->case_no} rejected.");

        return response()->json([
            'success' => true,
            'message' => 'Case rejected successfully'
        ]);
    }

    // ==========================================
    // 💸 2. OFFICE EXPENSES
    // ==========================================

    public function expenseIndex(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-d'));
        $limit = max(1, (int)$request->input('limit', 15));

        $query = DB::table('expenses as e')
            ->whereNull('e.deleted_at')
            ->leftJoin('users as u', 'e.recorded_by', '=', 'u.id')
            ->leftJoin('units as un', 'e.unit_id', '=', 'un.id')
            ->select('e.*', 'u.full_name as recorded_by_name', 'un.plate_number')
            ->whereBetween('e.date', [$date_from, $date_to]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('e.description', 'like', '%' . $search . '%')
                  ->orWhere('e.category', 'like', '%' . $search . '%')
                  ->orWhere('e.reference_number', 'like', '%' . $search . '%')
                  ->orWhere('e.vendor_name', 'like', '%' . $search . '%');
            });
        }
        if (!empty($category)) {
            $query->where('e.category', $category);
        }

        $totals = (clone $query)
            ->select(DB::raw('SUM(e.amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
            ->first();

        $expenses = $query->orderByDesc('e.date')
            ->orderByDesc('e.created_at')
            ->paginate($limit);

        $categories = DB::table('expenses')->whereNull('deleted_at')->distinct()->pluck('category');

        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        $thisMonthAmount = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$thisMonth])
            ->sum('amount') ?? 0;

        $lastMonthAmount = DB::table('expenses')
            ->whereNull('deleted_at')
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$lastMonth])
            ->sum('amount') ?? 0;

        $changePercent = 0;
        if ($lastMonthAmount > 0) {
            $changePercent = round((($thisMonthAmount - $lastMonthAmount) / $lastMonthAmount) * 100, 1);
        }

        $stats = [
            'today' => DB::table('expenses')
                ->whereNull('deleted_at')
                ->whereDate('date', date('Y-m-d'))
                ->sum('amount') ?? 0,
            'this_month' => $thisMonthAmount,
            'last_month' => $lastMonthAmount,
            'monthly_change' => $changePercent,
            'total_records' => $totals->total_count,
            'by_category' => DB::table('expenses')
                ->selectRaw('category, COUNT(*) as count, SUM(amount) as total')
                ->whereNull('deleted_at')
                ->whereBetween('date', [$date_from, $date_to])
                ->groupBy('category')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'expenses' => $expenses,
            'stats' => $stats,
            'categories' => $categories,
            'totals' => $totals,
        ]);
    }

    public function expenseStore(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string|max:250',
            'vendor_name' => ['nullable', 'string', 'max:30'],
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'payment_method' => 'nullable|string',
            'date' => 'required|date',
            'reference_number' => ['nullable', 'string', 'max:30'],
            'unit_id' => 'nullable|integer',
            'spare_part_id' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'unit_price' => 'nullable|numeric',
        ]);

        $expense = Expense::create([
            'category' => $request->category,
            'description' => $request->description,
            'vendor_name' => $request->vendor_name,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'date' => $request->date,
            'reference_number' => $request->reference_number,
            'unit_id' => $request->unit_id ?: null,
            'spare_part_id' => is_numeric($request->spare_part_id) ? $request->spare_part_id : null,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'recorded_by' => auth()->id(),
            'created_by' => auth()->id(),
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Spare parts stock adjustment
        if ($request->category === 'Spare Parts Purchase' && is_numeric($request->spare_part_id) && $request->quantity > 0) {
            $part = \App\Models\SparePart::find($request->spare_part_id);
            if ($part) {
                $part->increment('stock_quantity', $request->quantity);
            }
        }

        ActivityLogController::log('Created Office Expense via Mobile', "Category: {$request->category}\nAmount: ₱" . number_format($request->amount, 2));

        return response()->json([
            'success' => true,
            'message' => 'Expense added successfully',
            'data' => $expense
        ]);
    }

    public function expenseUpdate(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string',
            'description' => 'required|string|max:250',
            'vendor_name' => ['nullable', 'string', 'max:30'],
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'payment_method' => 'nullable|string',
            'date' => 'required|date',
            'reference_number' => ['nullable', 'string', 'max:30'],
            'unit_id' => 'nullable|integer',
        ]);

        $expense = Expense::findOrFail($id);
        
        // Reverse old stock
        if ($expense->category === 'Spare Parts Purchase' && $expense->spare_part_id && $expense->quantity > 0) {
            $oldPart = \App\Models\SparePart::find($expense->spare_part_id);
            if ($oldPart) {
                $oldPart->decrement('stock_quantity', $expense->quantity);
            }
        }

        $expense->update([
            'category' => $request->category,
            'description' => $request->description,
            'vendor_name' => $request->vendor_name,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'date' => $request->date,
            'reference_number' => $request->reference_number,
            'unit_id' => $request->unit_id ?: null,
            'updated_by' => auth()->id(),
        ]);

        // Re-apply stock
        if ($expense->category === 'Spare Parts Purchase' && $expense->spare_part_id && $expense->quantity > 0) {
            $newPart = \App\Models\SparePart::find($expense->spare_part_id);
            if ($newPart) {
                $newPart->increment('stock_quantity', $expense->quantity);
            }
        }

        ActivityLogController::log('Updated Office Expense via Mobile', "Record #{$id}\nAmount: ₱" . number_format($expense->amount, 2));

        return response()->json([
            'success' => true,
            'message' => 'Expense updated successfully'
        ]);
    }

    public function expenseDestroy($id)
    {
        $expense = Expense::findOrFail($id);
        
        // Reverse stock
        if ($expense->category === 'Spare Parts Purchase' && $expense->spare_part_id && $expense->quantity > 0) {
            $part = \App\Models\SparePart::find($expense->spare_part_id);
            if ($part) {
                $part->decrement('stock_quantity', $expense->quantity);
            }
        }

        $expense->delete();
        ActivityLogController::log('Archived Office Expense via Mobile', "Record #{$id} archived.");

        return response()->json([
            'success' => true,
            'message' => 'Expense archived successfully'
        ]);
    }

    // ==========================================
    // 💼 3. SALARY MANAGEMENT
    // ==========================================

    public function salaryIndex(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $search = $request->input('search', '');

        $query = DB::table('salaries as s')
            ->leftJoin('users as u', function($join) {
                $join->on('s.employee_id', '=', 'u.id')->where('s.source', '=', 'user');
            })
            ->leftJoin('staff as st', function($join) {
                $join->on('s.employee_id', '=', 'st.id')->where('s.source', '=', 'staff');
            })
            ->where(function($q) {
                $q->whereNull('u.deleted_at')
                  ->orWhereNull('st.deleted_at');
            })
            ->select(
                's.*',
                DB::raw('COALESCE(u.full_name, st.name) as employee_name'),
                DB::raw('COALESCE(u.email, st.phone) as email'),
                's.employee_type as position',
                's.total_salary as total_pay'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.full_name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('s.employee_type', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search])
                    ->orWhere('st.name', 'like', DB::raw("CONCAT('%', ?, '%') COLLATE utf8mb4_unicode_ci"), [$search]);
            });
        }

        $salaries = $query->orderBy('s.created_at', 'desc')->get();

        $monthlyRecords = DB::table('salaries')
            ->where('month', $month)
            ->where('year', $year)
            ->get();
 
        $total_income = DB::table('boundaries')
            ->whereNull('deleted_at')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('actual_boundary') ?? 0;
 
        $total_salaries = $monthlyRecords->sum('total_salary');
        $employees_paid = $monthlyRecords->unique(function ($item) {
            return $item->source . '_' . $item->employee_id;
        })->count();

        $user_count = DB::table('users')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('role', '!=', 'driver')
            ->where('role', '!=', 'super_admin')
            ->count();

        $staff_count = DB::table('staff')
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->where('role', '!=', 'driver')
            ->count();

        $total_employees = $user_count + $staff_count;
        $net_profit = $total_income - $total_salaries;

        $summary = [
            'total_employees' => $total_employees,
            'total_salaries' => $total_salaries,
            'net_profit' => $net_profit,
            'avg_salary' => $employees_paid > 0 ? $total_salaries / $employees_paid : 0,
        ];

        $employees = DB::table('users')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('role', '!=', 'driver')
            ->where('role', '!=', 'super_admin')
            ->select('id', 'full_name as name', 'role', DB::raw("'user' as source"))
            ->union(
                DB::table('staff')
                    ->whereNull('deleted_at')
                    ->where('status', 'active')
                    ->where('role', '!=', 'driver')
                    ->select('id', 'name', 'role', DB::raw("'staff' as source"))
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'salaries' => $salaries,
            'summary' => $summary,
            'employees' => $employees,
        ]);
    }

    public function salaryStore(Request $request)
    {
        $data = $request->validate([
            'employee_raw' => 'required|string', // Expecting "user_5" or "staff_2"
            'employee_type' => 'required|string',
            'basic_salary' => 'required|integer|min:1|max:99999',
            'overtime_pay' => 'nullable|integer|min:1|max:99999',
            'holiday_pay' => 'nullable|integer|min:1|max:99999',
            'night_differential' => 'nullable|integer|min:1|max:99999',
            'allowance' => 'nullable|integer|min:1|max:99999',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'pay_date' => 'required|date',
        ]);

        $total_salary = $data['basic_salary'] 
            + ($data['overtime_pay'] ?? 0) 
            + ($data['holiday_pay'] ?? 0) 
            + ($data['night_differential'] ?? 0) 
            + ($data['allowance'] ?? 0);

        $parts = explode('_', $data['employee_raw'], 2);
        $source = count($parts) == 2 ? $parts[0] : 'user';
        $employee_id = count($parts) == 2 ? $parts[1] : $data['employee_raw'];

        DB::table('salaries')->insert([
            'employee_id' => $employee_id,
            'source' => $source,
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

        $employeeName = $source === 'user' 
            ? DB::table('users')->where('id', $employee_id)->value('full_name')
            : DB::table('staff')->where('id', $employee_id)->value('name');

        ActivityLogController::log('Processed Salary via Mobile', "Employee: {$employeeName}\nTotal: ₱" . number_format($total_salary, 2));

        return response()->json([
            'success' => true,
            'message' => 'Salary record processed successfully.'
        ]);
    }

    public function salaryUpdate(Request $request, $id)
    {
        $data = $request->validate([
            'employee_type' => 'required|string',
            'basic_salary' => 'required|integer|min:1|max:99999',
            'overtime_pay' => 'nullable|integer|min:1|max:99999',
            'holiday_pay' => 'nullable|integer|min:1|max:99999',
            'night_differential' => 'nullable|integer|min:1|max:99999',
            'allowance' => 'nullable|integer|min:1|max:99999',
            'pay_date' => 'required|date',
        ]);

        $total_salary = $data['basic_salary'] 
            + ($data['overtime_pay'] ?? 0) 
            + ($data['holiday_pay'] ?? 0) 
            + ($data['night_differential'] ?? 0) 
            + ($data['allowance'] ?? 0);

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

        ActivityLogController::log('Updated Salary Record via Mobile', "Record #{$id}\nTotal: ₱" . number_format($total_salary, 2));

        return response()->json([
            'success' => true,
            'message' => 'Salary record updated successfully.'
        ]);
    }

    public function salaryDestroy($id)
    {
        DB::table('salaries')->where('id', $id)->delete();
        ActivityLogController::log('Deleted Salary Record via Mobile', "Record #{$id} deleted.");

        return response()->json([
            'success' => true,
            'message' => 'Salary record deleted successfully'
        ]);
    }

    // ==========================================
    // 👥 4. STAFF RECORDS
    // ==========================================

    public function staffIndex(Request $request)
    {
        $search = $request->get('search', '');

        $adminQuery = User::query()->where('role', '!=', 'driver')->whereNull('deleted_at');
        if ($search) {
            $adminQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $adminStaff = $adminQuery->orderBy('full_name')->get();

        $generalQuery = Staff::query()->where('role', '!=', 'driver')->whereNull('deleted_at');
        if ($search) {
            $generalQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        $generalStaff = $generalQuery->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'adminStaff' => $adminStaff,
            'generalStaff' => $generalStaff,
        ]);
    }

    public function staffStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'role' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:1,11',
            'contact_person' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'emergency_phone' => 'nullable|numeric|digits_between:1,11',
            'address' => 'nullable|string|max:200',
            'status' => 'required|in:active,inactive',
        ]);

        $staff = Staff::create($data);

        ActivityLogController::log('Created Staff Record via Mobile', "Name: {$data['name']}\nRole: {$data['role']}");

        return response()->json([
            'success' => true,
            'message' => 'Staff record created successfully',
            'data' => $staff
        ]);
    }

    public function staffUpdate(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'role' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:1,11',
            'contact_person' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z]+( [A-Za-z]+){0,5}$/'],
            'emergency_phone' => 'nullable|numeric|digits_between:1,11',
            'address' => 'nullable|string|max:200',
            'status' => 'required|in:active,inactive',
        ]);

        $staff->update($data);

        ActivityLogController::log('Updated Staff Record via Mobile', "Name: {$staff->name}\nRole: {$staff->role}");

        return response()->json([
            'success' => true,
            'message' => 'Staff record updated successfully'
        ]);
    }

    public function staffDestroy($id)
    {
        $staff = Staff::findOrFail($id);
        $name = $staff->name;
        $staff->delete();

        ActivityLogController::log('Archived Staff Record via Mobile', "Staff: {$name} moved to archive.");

        return response()->json([
            'success' => true,
            'message' => 'Staff record archived successfully'
        ]);
    }

    // ==========================================
    // 🗓️ 5. CODING MANAGEMENT
    // ==========================================

    public function codingIndex(Request $request)
    {
        $search = $request->input('search', '');
        $today_name = now()->timezone('Asia/Manila')->format('l');

        $rules = DB::table('coding_rules')
            ->when($search, function($q) use ($search) {
                $q->where('coding_day', 'like', "%{$search}%")
                  ->orWhere('restricted_plate_numbers', 'like', "%{$search}%");
            })
            ->orderBy('coding_day')
            ->get();

        $full_fleet = DB::table('units as u')
            ->leftJoin('drivers as drv1', 'u.driver_id', '=', 'drv1.id')
            ->select('u.*', DB::raw("CONCAT(COALESCE(drv1.first_name,''), ' ', COALESCE(drv1.last_name,'')) as driver1_name"))
            ->get();

        foreach ($full_fleet as $u) {
            if (empty($u->coding_day)) {
                $u->coding_day = $this->deriveCodingDay($u->plate_number);
            }
        }

        $coding_today_count = $full_fleet->where('coding_day', $today_name)->count();
        $total_fleet_count = $full_fleet->where('status', '!=', 'Inactive')->count();

        $stats = [
            'today_coding' => $coding_today_count,
            'on_road' => max(0, $total_fleet_count - $coding_today_count),
            'active_coding_fleet' => $full_fleet->where('coding_day', $today_name)->where('status', 'Available')->count(),
        ];

        return response()->json([
            'success' => true,
            'rules' => $rules,
            'fleet' => $full_fleet,
            'stats' => $stats,
            'today_name' => $today_name,
        ]);
    }

    public function codingUpdateDay(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|integer',
            'coding_day' => 'required|string',
        ]);

        DB::table('units')->where('id', $request->unit_id)->update([
            'coding_day' => $request->coding_day,
            'coding_updated_at' => now(),
            'updated_at' => now(),
        ]);

        $plate = DB::table('units')->where('id', $request->unit_id)->value('plate_number');
        ActivityLogController::log('Updated Coding Day via Mobile', "Unit: {$plate} changed to {$request->coding_day}");

        return response()->json([
            'success' => true,
            'message' => 'Coding day updated successfully'
        ]);
    }

    private function deriveCodingDay($plate)
    {
        $lastDigit = @substr(preg_replace('/[^0-9]/', '', $plate), -1);
        if ($lastDigit === false || $lastDigit === '') return 'Unknown';
        if ($lastDigit == 1 || $lastDigit == 2) return 'Monday';
        if ($lastDigit == 3 || $lastDigit == 4) return 'Tuesday';
        if ($lastDigit == 5 || $lastDigit == 6) return 'Wednesday';
        if ($lastDigit == 7 || $lastDigit == 8) return 'Thursday';
        if ($lastDigit == 9 || $lastDigit == 0) return 'Friday';
        return 'Unknown';
    }

    // ==========================================
    // 📈 6. UNIT PROFITABILITY & AI DSS
    // ==========================================

    public function profitabilityIndex(Request $request)
    {
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-t'));
        $unit_filter = $request->input('unit', '');

        $where_clause = "WHERE u.deleted_at IS NULL";
        $params = [$date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to];
        
        if (!empty($unit_filter)) {
            $where_clause .= " AND u.plate_number = ?";
            $params[] = $unit_filter;
        }

        $sql = "SELECT 
                u.id, u.plate_number, COALESCE(u.make, 'Unknown') as make, COALESCE(u.model, 'Unknown') as model, COALESCE(u.year, 0) as year, COALESCE(u.purchase_cost, 0) as purchase_cost, COALESCE(u.boundary_rate, 0) as boundary_rate,
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

        $profitability = DB::select($sql, $params);

        foreach ($profitability as &$unit) {
            $unit->net_income = $unit->total_boundary - $unit->total_maintenance - $unit->total_expenses;
            $unit->profit_margin = $unit->total_boundary > 0 ? (($unit->net_income / $unit->total_boundary) * 100) : 0;
            $unit->roi_percentage = $unit->purchase_cost > 0 ? (($unit->net_income / $unit->purchase_cost) * 100) : 0;
            $unit->payback_period = $unit->total_boundary > 0 ? ($unit->purchase_cost / $unit->total_boundary) : 0;
            $unit->roi_achieved = $unit->purchase_cost > 0 && $unit->net_income >= $unit->purchase_cost ? 1 : 0;
        }

        $overview = [
            'total_boundary' => array_sum(array_column($profitability, 'total_boundary')),
            'total_maintenance' => array_sum(array_column($profitability, 'total_maintenance')),
            'total_expenses' => array_sum(array_column($profitability, 'total_expenses')),
            'net_income' => array_sum(array_column($profitability, 'net_income')),
            'total_units' => count($profitability),
            'avg_margin' => count($profitability) > 0 ? array_sum(array_column($profitability, 'profit_margin')) / count($profitability) : 0,
        ];

        return response()->json([
            'success' => true,
            'profitability' => $profitability,
            'overview' => $overview,
        ]);
    }

    public function profitabilityDetails(Request $request)
    {
        $unit_id = $request->unit_id;
        $date_from = $request->date_from ?? date('Y-m-01');
        $date_to = $request->date_to ?? date('Y-m-d');

        $unit = DB::table('units')->where('id', $unit_id)->first();
        if (!$unit) {
            return response()->json(['error' => 'Unit not found'], 404);
        }

        $boundaries = DB::table('boundaries')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->orderBy('date', 'desc')
            ->get();

        $maintenances = DB::table('maintenance')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date_started', [$date_from, $date_to])
            ->orderBy('date_started', 'desc')
            ->get();

        $expenses = DB::table('expenses')
            ->where('unit_id', $unit_id)
            ->whereNull('deleted_at')
            ->whereBetween('date', [$date_from, $date_to])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'unit' => $unit,
            'boundaries' => $boundaries,
            'maintenances' => $maintenances,
            'expenses' => $expenses,
        ]);
    }

    public function generateAiDss(Request $request)
    {
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-t'));

        $stats = DB::table('units as u')
            ->leftJoin('boundaries as b', function($join) use ($date_from, $date_to) {
                $join->on('u.id', '=', 'b.unit_id')->whereBetween('b.date', [$date_from, $date_to])->whereNull('b.deleted_at');
            })
            ->leftJoin('maintenance as m', function($join) use ($date_from, $date_to) {
                $join->on('u.id', '=', 'm.unit_id')->whereBetween('m.date_started', [$date_from, $date_to])->whereNull('m.deleted_at');
            })
            ->select(
                'u.plate_number', 'u.make', 'u.model', 'u.purchase_cost',
                DB::raw('COALESCE(SUM(b.actual_boundary), 0) as total_revenue'),
                DB::raw('COALESCE(SUM(m.cost), 0) as total_maintenance'),
                DB::raw('COUNT(DISTINCT b.id) as active_days')
            )
            ->whereNull('u.deleted_at')
            ->groupBy('u.id', 'u.plate_number', 'u.make', 'u.model', 'u.purchase_cost')
            ->get();

        $totalUnits = $stats->count();
        $totalRevenue = $stats->sum('total_revenue');
        $totalMaint = $stats->sum('total_maintenance');
        
        if ($totalUnits === 0) {
            return response()->json(['success' => false, 'message' => 'No unit data available for the selected period.']);
        }

        $topPerformer = $stats->sortByDesc('total_revenue')->first();
        $worstPerformer = $stats->sortBy('total_revenue')->first();

        $prompt = "As a Taxi Fleet Financial Analyst AI, analyze this profitability data for $totalUnits units from $date_from to $date_to:
        - Total Revenue: ₱" . number_format($totalRevenue, 2) . "
        - Total Maintenance Cost: ₱" . number_format($totalMaint, 2) . "
        - Top Performer: " . ($topPerformer->plate_number ?? 'N/A') . " (₱" . number_format($topPerformer->total_revenue ?? 0, 2) . ")
        - Lowest Revenue: " . ($worstPerformer->plate_number ?? 'N/A') . " (₱" . number_format($worstPerformer->total_revenue ?? 0, 2) . ")
        
        Provide a strategic Decision Support (DSS) report including:
        1. Financial Health Score (1-100).
        2. Top 3 Revenue Leakage risks identified.
        3. Strategic recommendations for fleet maintenance vs replacement.
        4. ROI projection based on current performance.
        
        Keep it professional, data-driven, and highly actionable. Format in HTML-ready markdown.";

        try {
            $aiResponse = GeminiService::generate($prompt);
            return response()->json(['success' => true, 'analysis' => $aiResponse]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'AI Service currently unavailable.'], 500);
        }
    }

    // ==========================================
    // 🚨 7. DRIVER BEHAVIOR (INCIDENT MANAGEMENT)
    // ==========================================

    public function incidentIndex(Request $request)
    {
        $search          = $request->input('search', '');
        $type_filter     = $request->input('type', '');
        $severity_filter = $request->input('severity', '');
        $date_from       = $request->input('date_from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $date_to         = $request->input('date_to', now()->timezone('Asia/Manila')->toDateString());
        $page            = max(1, (int) $request->input('page', 1));
        $limit           = (int) $request->input('limit', 15);
        $offset          = ($page - 1) * $limit;

        $query = DriverBehavior::query()
            ->with(['involvedParties', 'partsEstimates.part'])
            ->leftJoin('units as u', 'driver_behavior.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'driver_behavior.driver_id', '=', 'd.id')
            ->select(
                'driver_behavior.*',
                'u.plate_number',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name")
            );

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,'')))"), 'like', "%{$search}%")
                  ->orWhere('u.plate_number', 'like', "%{$search}%")
                  ->orWhere('driver_behavior.incident_type', 'like', "%{$search}%")
                  ->orWhere('driver_behavior.description', 'like', "%{$search}%");
            });
        }

        if (!empty($type_filter)) {
            $query->where('driver_behavior.incident_type', $type_filter);
        }

        if (!empty($severity_filter)) {
            $query->where('driver_behavior.severity', $severity_filter);
        }

        if (!empty($date_from)) {
            $query->whereDate('driver_behavior.timestamp', '>=', $date_from);
        }
        if (!empty($date_to)) {
            $query->whereDate('driver_behavior.timestamp', '<=', $date_to);
        }

        $total     = $query->count();
        $incidents = $query->orderByDesc('driver_behavior.timestamp')->offset($offset)->limit($limit)->get();

        $drivers = DB::table('drivers')->whereNull('deleted_at')->where('driver_status', '!=', 'banned')->get();
        $units = DB::table('units')->whereNull('deleted_at')->get();
        $classifications = DB::table('incident_classifications')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'incidents' => $incidents,
            'drivers' => $drivers,
            'units' => $units,
            'classifications' => $classifications,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => $total,
                'total_pages' => max(1, (int) ceil($total / $limit)),
            ]
        ]);
    }

    public function incidentStore(Request $request)
    {
        $data = $request->validate([
            'unit_id'                => 'required|integer',
            'driver_id'              => 'required|integer',
            'incident_type'          => 'required|string',
            'sub_classification'     => 'nullable|string|max:255',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'traffic_fine_amount'    => 'nullable|numeric|min:0',
            'third_party_name'       => 'nullable|string',
            'third_party_vehicle'    => 'nullable|string',
            'own_unit_damage_cost'   => 'nullable|numeric|min:0',
            'third_party_damage_cost'=> 'nullable|numeric|min:0',
            'is_driver_fault'        => 'nullable|boolean',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
            'charge_status'          => 'nullable|string',
            'latitude'               => 'nullable|numeric',
            'longitude'              => 'nullable|numeric',
            'video_url'              => 'nullable|string',
        ]);

        $classification = DB::table('incident_classifications')->where('name', $data['incident_type'])->first();
        $behaviorMode = $classification?->behavior_mode ?? 'narrative';

        $isFault = (bool)($data['is_driver_fault'] ?? false);
        $isDamage = $behaviorMode === 'damage';
        $isTraffic = $behaviorMode === 'traffic';

        $totalCharge = 0;
        if ($isDamage) {
            $totalCharge = $data['total_charge_to_driver'] ?? 0;
        } elseif ($isTraffic) {
            $totalCharge = (float)($data['traffic_fine_amount'] ?? 0);
        }

        $behavior = DriverBehavior::create([
            'unit_id'                 => $data['unit_id'],
            'driver_id'               => $data['driver_id'],
            'incident_type'           => $data['incident_type'],
            'sub_classification'      => $data['sub_classification'] ?? null,
            'traffic_fine_amount'     => $isTraffic ? ($data['traffic_fine_amount'] ?? 0) : null,
            'severity'                => $data['severity'],
            'description'             => $data['description'],
            'own_unit_damage_cost'    => $isDamage ? ($data['own_unit_damage_cost'] ?? 0) : 0,
            'third_party_damage_cost' => $isDamage ? ($data['third_party_damage_cost'] ?? 0) : 0,
            'is_driver_fault'         => $isFault,
            'total_charge_to_driver'  => $totalCharge,
            'total_paid'              => 0,
            'remaining_balance'       => $totalCharge,
            'charge_status'           => $totalCharge > 0 ? 'pending' : 'none',
            'latitude'                => $data['latitude'] ?? 0,
            'longitude'               => $data['longitude'] ?? 0,
            'video_url'               => $data['video_url'] ?? '',
            'timestamp'               => now()->timezone('Asia/Manila'),
            'incident_date'           => $data['incident_date'] ?? now()->timezone('Asia/Manila')->toDateString(),
        ]);

        // Void incentive for violation day
        if ($behavior->isViolation() && !empty($data['incident_date'])) {
            DB::table('boundaries')
                ->where('driver_id', $data['driver_id'])
                ->whereDate('date', $data['incident_date'])
                ->update([
                    'has_incentive'         => false,
                    'counted_for_incentive' => false,
                    'notes'                 => DB::raw("CONCAT(COALESCE(notes,''), ' [Disqualified: Recorded Violation - {$data['incident_type']}]')")
                ]);
        }

        // Auto Ban triggers
        $shouldAutoBan = false;
        if (strtolower($data['severity']) === 'critical') {
            $shouldAutoBan = true;
        }

        if ($shouldAutoBan) {
            DB::table('drivers')->where('id', $data['driver_id'])->update(['driver_status' => 'banned']);
            DB::table('units')->where('driver_id', $data['driver_id'])->update(['driver_id' => null, 'updated_at' => now()]);
            DB::table('units')->where('secondary_driver_id', $data['driver_id'])->update(['secondary_driver_id' => null, 'updated_at' => now()]);

            DB::table('system_alerts')->insert([
                'title'       => "🚫 MOBILE AUTO-BAN",
                'message'     => "Driver has been automatically banned due to critical violation logged via mobile.",
                'type'        => 'danger',
                'is_resolved' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $driverName = DB::table('drivers')->where('id', $data['driver_id'])->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
        ActivityLogController::log('Recorded Incident via Mobile', "Driver: {$driverName}\nType: {$data['incident_type']}");

        return response()->json([
            'success' => true,
            'message' => 'Incident recorded successfully' . ($shouldAutoBan ? ' and driver has been BANNED.' : '')
        ]);
    }

    public function incidentShow($id)
    {
        $incident = DriverBehavior::with(['involvedParties', 'partsEstimates'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'incident' => $incident
        ]);
    }

    public function incidentUpdate(Request $request, $id)
    {
        $incident = DriverBehavior::findOrFail($id);
        
        $data = $request->validate([
            'incident_type'          => 'required|string',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'is_driver_fault'        => 'nullable|boolean',
            'sub_classification'     => 'nullable|string',
            'traffic_fine_amount'    => 'nullable|numeric|min:0',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
        ]);

        $incident->update([
            'incident_type'          => $data['incident_type'],
            'severity'               => $data['severity'],
            'description'            => $data['description'],
            'is_driver_fault'        => (bool)($data['is_driver_fault'] ?? false),
            'sub_classification'     => $data['sub_classification'] ?? $incident->sub_classification,
            'traffic_fine_amount'    => $data['traffic_fine_amount'] ?? 0,
            'total_charge_to_driver' => $data['total_charge_to_driver'] ?? 0,
            'remaining_balance'      => $data['total_charge_to_driver'] ?? 0,
            'incident_date'          => $data['incident_date'] ?? $incident->incident_date,
        ]);

        ActivityLogController::log('Updated Incident via Mobile', "Incident #{$id} updated.");

        return response()->json([
            'success' => true,
            'message' => 'Incident updated successfully'
        ]);
    }

    public function incidentDestroy($id)
    {
        $incident = DriverBehavior::findOrFail($id);
        $incident->delete();

        ActivityLogController::log('Archived Incident via Mobile', "Incident #{$id} moved to archive.");

        return response()->json([
            'success' => true,
            'message' => 'Incident archived successfully'
        ]);
    }
}
