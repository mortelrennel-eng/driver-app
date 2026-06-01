<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\ActivityLogController;

class FranchiseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', ''); // active, soon, expired
        
        $hasUnitsTable = Schema::hasTable('franchise_case_units');

        $query = DB::table('franchise_cases')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('case_no', 'like', "%{$search}%")
                  ->orWhere('type_of_application', 'like', "%{$search}%")
                  ->orWhere('denomination', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            if ($statusFilter === 'active') {
                $query->where(function($q) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString());
                });
            } elseif ($statusFilter === 'soon') {
                $query->whereNotNull('expiry_date')
                      ->whereRaw('expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
            } elseif ($statusFilter === 'expired') {
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '<', now()->toDateString());
            }
        }

        $casesCollection = $query->orderByDesc('created_at')->get();
        
        $cases = collect($casesCollection)->map(function($c) use ($hasUnitsTable) {
            $row = (array)$c;
            if ($hasUnitsTable) {
                $units = DB::table('franchise_case_units')
                    ->where('franchise_case_id', $row['id'])
                    ->get()
                    ->toArray();
                $row['units'] = $units;
                $row['unit_count'] = count($units);
            } else {
                $row['units'] = [];
                $row['unit_count'] = 0;
            }
            
            // Determine display status
            $expiry = $row['expiry_date'] ? \Carbon\Carbon::parse($row['expiry_date']) : null;
            if ($expiry && $expiry->isPast()) {
                $row['display_status'] = 'EXPIRED';
            } elseif ($expiry && $expiry->diffInDays(now()) <= 30) {
                $row['display_status'] = 'SOON';
            } else {
                $row['display_status'] = 'ACTIVE';
            }

            return $row;
        });

        // Statistics
        $stats = [
            'total' => DB::table('franchise_cases')->whereNull('deleted_at')->count(),
            'active' => DB::table('franchise_cases')
                ->whereNull('deleted_at')
                ->where(function($q) {
                    $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now()->toDateString());
                })->count(),
            'soon' => DB::table('franchise_cases')
                ->whereNull('deleted_at')
                ->whereNotNull('expiry_date')
                ->whereRaw('expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')
                ->count(),
            'expired' => DB::table('franchise_cases')
                ->whereNull('deleted_at')
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now()->toDateString())
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $cases,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'case_no' => 'required|string|max:100',
            'type_of_application' => 'required|string|max:255',
            'denomination' => 'required|string|max:255',
            'date_filed' => 'required|date',
            'expiry_date' => 'nullable|date',
            'units' => 'nullable|array',
        ]);

        // Check for duplicate case_no
        $duplicateCase = DB::table('franchise_cases')
            ->where('case_no', $request->case_no)
            ->whereNull('deleted_at')
            ->first();
            
        if ($duplicateCase) {
            return response()->json(['success' => false, 'message' => 'Error: Case No. "' . $request->case_no . '" already exists.'], 422);
        }

        // Check for duplicate plate numbers
        if (!empty($validated['units'])) {
            $incomingPlates = collect($validated['units'])->map(fn($u) => strtoupper(trim($u['plate_no'] ?? '')))->filter()->toArray();
            if (!empty($incomingPlates)) {
                $duplicatePlate = DB::table('franchise_case_units')
                    ->whereIn('plate_no', $incomingPlates)
                    ->join('franchise_cases', 'franchise_case_units.franchise_case_id', '=', 'franchise_cases.id')
                    ->whereNull('franchise_cases.deleted_at')
                    ->first();
                if ($duplicatePlate) {
                    return response()->json(['success' => false, 'message' => 'Error: Plate No. "' . $duplicatePlate->plate_no . '" is already registered in Case No. "' . $duplicatePlate->case_no . '".'], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $id = DB::table('franchise_cases')->insertGetId([
                'applicant_name' => $validated['applicant_name'],
                'case_no' => $validated['case_no'],
                'type_of_application' => $validated['type_of_application'],
                'denomination' => $validated['denomination'],
                'date_filed' => $validated['date_filed'],
                'expiry_date' => $validated['expiry_date'] ?: null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($validated['units']) && Schema::hasTable('franchise_case_units')) {
                foreach ($validated['units'] as $u) {
                    if (!empty($u['make']) || !empty($u['motor_no']) || !empty($u['plate_no'])) {
                        DB::table('franchise_case_units')->insert([
                            'franchise_case_id' => $id,
                            'make' => $u['make'] ?? '',
                            'motor_no' => $u['motor_no'] ?? '',
                            'chasis_no' => $u['chasis_no'] ?? '',
                            'plate_no' => $u['plate_no'] ?? '',
                            'year_model' => $u['year_model'] ?? '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            ActivityLogController::log('Created Franchise Case', "Case No: {$validated['case_no']} created via Mobile App.");
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Franchise case added successfully.', 'id' => $id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $case = DB::table('franchise_cases')->where('id', $id)->whereNull('deleted_at')->first();
        if (!$case) {
            return response()->json(['success' => false, 'message' => 'Case not found.'], 404);
        }

        $units = [];
        if (Schema::hasTable('franchise_case_units')) {
            $units = DB::table('franchise_case_units')->where('franchise_case_id', $id)->get();
        }

        return response()->json([
            'success' => true,
            'data' => array_merge((array)$case, ['units' => $units])
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string|max:255',
            'case_no' => 'required|string|max:100',
            'type_of_application' => 'required|string|max:255',
            'denomination' => 'required|string|max:255',
            'date_filed' => 'required|date',
            'expiry_date' => 'nullable|date',
            'units' => 'nullable|array',
        ]);

        $case = DB::table('franchise_cases')->where('id', $id)->first();
        if (!$case) {
            return response()->json(['success' => false, 'message' => 'Case not found.'], 404);
        }

        // Check for duplicate plate numbers
        if (!empty($validated['units'])) {
            $incomingPlates = collect($validated['units'])->map(fn($u) => strtoupper(trim($u['plate_no'] ?? '')))->filter()->toArray();
            if (!empty($incomingPlates)) {
                $duplicatePlate = DB::table('franchise_case_units')
                    ->whereIn('plate_no', $incomingPlates)
                    ->where('franchise_case_id', '!=', $id)
                    ->join('franchise_cases', 'franchise_case_units.franchise_case_id', '=', 'franchise_cases.id')
                    ->whereNull('franchise_cases.deleted_at')
                    ->first();
                if ($duplicatePlate) {
                    return response()->json(['success' => false, 'message' => 'Error: Plate No. "' . $duplicatePlate->plate_no . '" is already registered in Case No. "' . $duplicatePlate->case_no . '".'], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            DB::table('franchise_cases')->where('id', $id)->update([
                'applicant_name' => $validated['applicant_name'],
                'case_no' => $validated['case_no'],
                'type_of_application' => $validated['type_of_application'],
                'denomination' => $validated['denomination'],
                'date_filed' => $validated['date_filed'],
                'expiry_date' => $validated['expiry_date'] ?: null,
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('franchise_case_units')) {
                DB::table('franchise_case_units')->where('franchise_case_id', $id)->delete();
                if (!empty($validated['units'])) {
                    foreach ($validated['units'] as $u) {
                        if (!empty($u['make']) || !empty($u['motor_no']) || !empty($u['plate_no'])) {
                            DB::table('franchise_case_units')->insert([
                                'franchise_case_id' => $id,
                                'make' => $u['make'] ?? '',
                                'motor_no' => $u['motor_no'] ?? '',
                                'chasis_no' => $u['chasis_no'] ?? '',
                                'plate_no' => $u['plate_no'] ?? '',
                                'year_model' => $u['year_model'] ?? '',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            ActivityLogController::log('Updated Franchise Case', "Case No: {$validated['case_no']} updated via Mobile App.");
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Franchise case updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $case = DB::table('franchise_cases')->where('id', $id)->first();
        if (!$case) {
            return response()->json(['success' => false, 'message' => 'Case not found.'], 404);
        }

        DB::table('franchise_cases')->where('id', $id)->update(['deleted_at' => now()]);
        ActivityLogController::log('Archived Franchise Case', "Case No: {$case->case_no} moved to archive via Mobile App.");

        return response()->json(['success' => true, 'message' => 'Franchise case archived successfully.']);
    }

    public function approve($id)
    {
        DB::table('franchise_cases')->where('id', $id)->update(['status' => 'approved', 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Case approved successfully.']);
    }

    public function reject($id)
    {
        DB::table('franchise_cases')->where('id', $id)->update(['status' => 'rejected', 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Case rejected successfully.']);
    }
}
