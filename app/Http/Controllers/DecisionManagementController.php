<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ActivityLogController;

class DecisionManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $page = max(1, (int) $request->input('page', 1));
        $limit = 1000; // Removed pagination limit so grouped expiry dates work correctly across all cases
        $offset = ($page - 1) * $limit;

        $hasUnitsTable = Schema::hasTable('franchise_case_units');

        // NEW: Handle loading a case for editing
        $edit_case = null;
        $edit_units = [];
        if ($request->has('id')) {
            $caseRecord = DB::table('franchise_cases')->where('id', $request->id)->first();
            if ($caseRecord) {
                $edit_case = (array) $caseRecord;
                if ($hasUnitsTable) {
                    $edit_units = DB::table('franchise_case_units')
                        ->where('franchise_case_id', $request->id)
                        ->get()
                        ->map(fn($u) => (array)$u)
                        ->toArray();
                }
            }
        }

        // Real columns: id, applicant_name, case_no, type_of_application, denomination, date_filed, expiry_date
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
        
        // Convert cases and add unit_count safely
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

        // Get statistics
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

        $totalPages = (int) ceil($total / $limit);
        $pagination = [
            'page' => $page,
            'total_pages' => $totalPages ?: 1,
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        return view('decision-management.index', compact('cases', 'search', 'pagination', 'stats', 'edit_case', 'edit_units'));
    }

    public function store(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'delete_case') {
            return $this->destroy($request->input('case_id'));
        }

        $caseId = $request->input('case_id');

        // Check for duplicate case_no
        $duplicateCase = DB::table('franchise_cases')
            ->where('case_no', $request->case_no)
            ->whereNull('deleted_at')
            ->when($caseId > 0, function($q) use ($caseId) {
                return $q->where('id', '!=', $caseId);
            })
            ->first();
            
        if ($duplicateCase) {
            return redirect()->back()->withInput()->with('error', 'Error: Case No. "' . $request->case_no . '" already exists in the system.');
        }

        // Check for duplicate plate numbers in units attached to other active franchise cases
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
                    ->when($caseId > 0, function($q) use ($caseId) {
                        return $q->where('franchise_case_id', '!=', $caseId);
                    })
                    ->join('franchise_cases', 'franchise_case_units.franchise_case_id', '=', 'franchise_cases.id')
                    ->whereNull('franchise_cases.deleted_at')
                    ->first();

                if ($duplicatePlate) {
                    return redirect()->back()->withInput()->with('error', 'Error: Plate No. "' . $duplicatePlate->plate_no . '" is already registered in Case No. "' . $duplicatePlate->case_no . '".');
                }
            }
        }

        // Normalize (trim + uppercase where needed) before validating/saving
        $request->merge([
            'applicant_name' => trim((string) $request->input('applicant_name', '')),
            'case_no' => trim((string) $request->input('case_no', '')),
            'type_of_application' => trim((string) $request->input('type_of_application', '')),
            'denomination' => trim((string) $request->input('denomination', '')),
            'date_filed' => $request->input('date_filed'),
            'expiry_date' => $request->input('expiry_date'),
        ]);

        $validator = Validator::make($request->all(), [
            // Name-like fields: letters only; allowed punctuation: . ,
            'applicant_name' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'type_of_application' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],
            'denomination' => ['required', 'string', 'max:35', 'regex:/^(?=.*[A-Za-z])[A-Za-z., ]+$/'],

            // Case No: digits only, max 25
            'case_no' => ['required', 'string', 'max:25', 'regex:/^\d+$/'],

            // Dates required
            'date_filed' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:date_filed'],

            // Units array is optional; row-level validation handled in after()
            'units' => ['nullable', 'array'],
        ], [
            'applicant_name.regex' => 'Name of Applicant: letters only. Allowed: space, period (.), comma (,).',
            'type_of_application.regex' => 'Type of Application: letters only. Allowed: space, period (.), comma (,).',
            'denomination.regex' => 'Denomination: letters only. Allowed: space, period (.), comma (,).',
            'case_no.regex' => 'Case No.: numbers only (no spaces).',
            'expiry_date.after_or_equal' => 'Expiry Date must be the same as or after Date Filed.',
        ]);

        $validator->after(function ($v) use ($request) {
            $units = $request->input('units', []);
            if (!is_array($units)) return;

            foreach ($units as $idx => $u) {
                if (!is_array($u)) continue;

                $make = trim((string)($u['make'] ?? ''));
                $motor = strtoupper(trim((string)($u['motor_no'] ?? '')));
                $chasis = strtoupper(trim((string)($u['chasis_no'] ?? '')));
                $plate = strtoupper(trim((string)($u['plate_no'] ?? '')));
                $year = trim((string)($u['year_model'] ?? ''));

                $anyFilled = ($make !== '' || $motor !== '' || $chasis !== '' || $plate !== '' || $year !== '');
                if (!$anyFilled) continue;

                // If row started, all are required
                if ($make === '') $v->errors()->add("units.$idx.make", "Row " . ($idx + 1) . " MAKE is required.");
                if ($motor === '') $v->errors()->add("units.$idx.motor_no", "Row " . ($idx + 1) . " MOTOR NO. is required.");
                if ($chasis === '') $v->errors()->add("units.$idx.chasis_no", "Row " . ($idx + 1) . " CHASIS NO. is required.");
                if ($plate === '') $v->errors()->add("units.$idx.plate_no", "Row " . ($idx + 1) . " PLATE NO. is required.");
                if ($year === '') $v->errors()->add("units.$idx.year_model", "Row " . ($idx + 1) . " YEAR MODEL is required.");

                // Field rules
                if ($make !== '' && (strlen($make) > 10 || !preg_match('/^(?=.*[A-Za-z])[A-Za-z ]+$/', $make))) {
                    $v->errors()->add("units.$idx.make", "Row " . ($idx + 1) . " MAKE must be letters only (spaces allowed), max 10.");
                }
                if ($motor !== '' && (strlen($motor) > 15 || !preg_match('/^[A-Z0-9]+$/', $motor))) {
                    $v->errors()->add("units.$idx.motor_no", "Row " . ($idx + 1) . " MOTOR NO. must be uppercase letters & numbers only, no spaces/symbols, max 15.");
                }
                if ($chasis !== '' && (strlen($chasis) > 15 || !preg_match('/^[A-Z0-9]+$/', $chasis))) {
                    $v->errors()->add("units.$idx.chasis_no", "Row " . ($idx + 1) . " CHASIS NO. must be uppercase letters & numbers only, no spaces/symbols, max 15.");
                }
                if ($plate !== '' && (strlen($plate) > 5 || !preg_match('/^[A-Z0-9]+$/', $plate))) {
                    $v->errors()->add("units.$idx.plate_no", "Row " . ($idx + 1) . " PLATE NO. must be letters & numbers only, no spaces/symbols, max 5.");
                }
                if ($year !== '' && !preg_match('/^\d{4}$/', $year)) {
                    $v->errors()->add("units.$idx.year_model", "Row " . ($idx + 1) . " YEAR MODEL must be 4 digits.");
                }
            }
        });

        $validator->validate();

        $data = [
            'applicant_name' => $request->applicant_name,
            'case_no' => $request->case_no,
            'type_of_application' => $request->type_of_application,
            'denomination' => $request->denomination,
            'date_filed' => $request->date_filed,
            'expiry_date' => $request->expiry_date,
            'updated_at' => now(),
        ];

        if ($caseId > 0) {
            DB::table('franchise_cases')->where('id', $caseId)->update($data);
            $id = $caseId;
            $message = 'Case updated successfully';
        } else {
            $data['created_at'] = now();
            $id = DB::table('franchise_cases')->insertGetId($data);
            $message = 'Case added successfully';
        }

        // Save units if provided and table exists
        if ($request->has('units') && Schema::hasTable('franchise_case_units')) {
            // Delete old units for this case
            DB::table('franchise_case_units')->where('franchise_case_id', $id)->delete();
            
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

        ActivityLogController::log(($caseId > 0 ? 'Updated Franchise Case' : 'Created Franchise Case'), "Case No: {$request->case_no}\nApplicant: {$request->applicant_name}\nType: {$request->type_of_application}");

        return redirect()->route('decision-management.index')->with('success', $message);
    }

    public function update(Request $request, $id)
    {
        // For RESTful compatibility
        return $this->store($request->merge(['case_id' => $id]));
    }

    public function destroy($id)
    {
        if (Schema::hasTable('franchise_case_units')) {
            DB::table('franchise_case_units')->where('franchise_case_id', $id)->delete();
        }
        
        $case = \App\Models\FranchiseCase::findOrFail($id);
        $caseNo = $case->case_no;
        $case->delete();
        
        ActivityLogController::log('Archived Franchise Case', "Case No: {$caseNo} moved to archive.");

        return redirect()->route('decision-management.index')->with('success', 'Case archived successfully');
    }

    public function approve($id)
    {
        DB::table('franchise_cases')->where('id', $id)->update([
            'status' => 'approved',
            'updated_at' => now(),
        ]);

        $caseNo = DB::table('franchise_cases')->where('id', $id)->value('case_no');
        ActivityLogController::log('Approved Franchise Case', "Case No: {$caseNo} has been approved.");

        return redirect()->route('decision-management.index')->with('success', 'Case approved successfully');
    }

    public function reject($id)
    {
        DB::table('franchise_cases')->where('id', $id)->update([
            'status' => 'rejected',
            'updated_at' => now(),
        ]);

        $caseNo = DB::table('franchise_cases')->where('id', $id)->value('case_no');
        ActivityLogController::log('Rejected Franchise Case', "Case No: {$caseNo} has been rejected.");

        return redirect()->route('decision-management.index')->with('success', 'Case rejected successfully');
    }
}
