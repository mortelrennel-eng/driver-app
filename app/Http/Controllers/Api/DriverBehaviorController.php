<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Driver;
use App\Models\DriverBehavior;
use App\Models\Unit;
use App\Models\SparePart;
use App\Models\IncidentClassification;
use App\Traits\CalculatesDriverPerformance;

class DriverBehaviorController extends Controller
{
    use CalculatesDriverPerformance;

    public function index(Request $request)
    {
        $search          = $request->input('search', '');
        $type_filter     = $request->input('type', '');
        $severity_filter = $request->input('severity', '');
        $date_from       = $request->input('date_from') ?: now()->timezone('Asia/Manila')->startOfMonth()->toDateString();
        $date_to         = $request->input('date_to') ?: now()->timezone('Asia/Manila')->toDateString();
        
        // Incidents
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

        if ($request->filled('date_from')) {
            $query->whereDate('driver_behavior.timestamp', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('driver_behavior.timestamp', '<=', $request->date_to);
        }

        $incidents = $query->orderByDesc('driver_behavior.timestamp')->get();

        // Stats
        $stats = $this->getStats($date_from, $date_to);

        // Incentive Summary
        $incentive_summary = $this->getIncentiveSummary();

        // Profiles
        $driver_profiles = $this->getDriverProfiles($date_from, $date_to);

        // Dropdowns for "Record Incident"
        $drivers = DB::table('drivers as d')
            ->leftJoin('units as u', function($j) {
                $j->on('d.id', '=', 'u.driver_id')->orOn('d.id', '=', 'u.secondary_driver_id');
            })
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->select('d.id', 
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as full_name"),
                'u.plate_number as current_plate'
            )
            ->orderBy('d.last_name')->get();

        $units = DB::table('units')->whereNull('deleted_at')->where('status', '!=', 'retired')
            ->select('id', 'plate_number', 'driver_id', 'secondary_driver_id')
            ->orderBy('plate_number')->get();

        $classifications = IncidentClassification::orderBy('name')->get();

        // Calculate next payout sunday (Global/Solo default)
        $now = now()->timezone('Asia/Manila');
        $payoutDate = $now->copy()->startOfMonth();
        while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) { $payoutDate->addDay(); }
        if ($now->gt($payoutDate->endOfDay())) {
            $payoutDate = $now->copy()->addMonth()->startOfMonth();
            while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) { $payoutDate->addDay(); }
        }

        return response()->json([
            'success' => true,
            'incidents' => $incidents,
            'stats' => $stats,
            'incentive_summary' => $incentive_summary,
            'driver_profiles' => $driver_profiles,
            'drivers' => $drivers,
            'units' => $units,
            'classifications' => $classifications,
            'disqualified_count' => count($incentive_summary['ineligible'] ?? []),
            'next_payout_sunday' => $payoutDate->format('M d, Y'),
            'date_range' => [
                'from' => $date_from,
                'to' => $date_to
            ],
            'debug' => [
                'total_incidents_count' => DriverBehavior::count(),
                'server_time' => now()->timezone('Asia/Manila')->toDateTimeString(),
                'date_from_used' => $date_from,
                'date_to_used' => $date_to,
                'violations_today_raw' => DB::table('driver_behavior')->whereDate('timestamp', now()->timezone('Asia/Manila')->format('Y-m-d'))->count()
            ]
        ]);
    }

    private function getStats($from, $to)
    {
        $today = now()->timezone('Asia/Manila')->toDateString();
        
        // Match Web: Using DB::table instead of Model to include soft-deleted records in stats
        $violationsToday = DB::table('driver_behavior')->whereDate('timestamp', $today)->count();
        
        $totalViolatorsQuery = DB::table('driver_behavior');
        if ($from) $totalViolatorsQuery->whereDate('timestamp', '>=', $from);
        if ($to) $totalViolatorsQuery->whereDate('timestamp', '<=', $to);
        
        $totalViolators = $totalViolatorsQuery->distinct('driver_id')
            ->count('driver_id');

        $totalCharges = (float)DB::table('driver_behavior')->sum('total_charge_to_driver');
        
        // Count eligible drivers for incentive
        $incentiveSummary = $this->getIncentiveSummary();
        $eligibleIncentive = count($incentiveSummary['eligible'] ?? []);

        return [
            'violations_today' => (int)$violationsToday,
            'total_violators' => (int)$totalViolators,
            'total_charges' => $totalCharges,
            'eligible_incentive' => (int)$eligibleIncentive,
        ];
    }

    private function getIncentiveSummary()
    {
        // Bulk fetch all relevant boundary counts for incentives
        $boundaryData = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as valid_days'))
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('valid_days', 'driver_id');

        // Bulk fetch violation counts (Behavioral)
        $violationData = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('count(*) as count'))
            ->where(function($q) {
                $q->where('is_driver_fault', 1)
                  ->orWhereIn('incident_type', DriverBehavior::VIOLATION_TYPES);
            })
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('count', 'driver_id');

        // Bulk fetch boundary violations (shortage/absent)
        $boundaryViolations = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as count'))
            ->where(function($q) {
                $q->where('shortage', '>', 0)
                  ->orWhere('has_incentive', false)
                  ->orWhere('is_absent', true);
            })
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('count', 'driver_id');

        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select('d.id', 'd.first_name', 'd.last_name', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id', 'u.id as unit_id')
            ->distinct('d.id')->get();

        $eligible   = [];
        $ineligible = [];

        foreach ($drivers as $d) {
            $valid_days = $boundaryData[$d->id] ?? 0;
            $vCount = ($violationData[$d->id] ?? 0) + ($boundaryViolations[$d->id] ?? 0);
            
            $is_eligible = $valid_days >= 20 && $vCount === 0;
            $is_dual = !empty($d->secondary_driver_id) && !empty($d->driver_id);

            $row = [
                'driver_id'     => $d->id,
                'name'          => trim($d->first_name . ' ' . $d->last_name),
                'unit'          => $d->plate_number,
                'driver_type'   => $is_dual ? 'Dual Driver' : 'Solo Driver',
                'valid_days'    => $valid_days,
                'violations'    => $vCount,
                'eligible'      => $is_eligible,
                'next_payout'   => 'Check Details', // Simplified for list performance
            ];

            if ($is_eligible) {
                $eligible[] = $row;
            } else {
                $ineligible[] = $row;
            }
        }

        return ['eligible' => $eligible, 'ineligible' => $ineligible];
    }

    private function getDriverProfiles($from, $to)
    {
        $incidentCounts = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $debtSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(remaining_balance) as aggregate'))
            ->where('charge_status', 'pending')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $shiftCounts = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereBetween('date', [$from, $to])
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $chargesSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(total_charge_to_driver) as aggregate'))
            ->whereBetween('timestamp', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $violationCounts = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereBetween('timestamp', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where(function($q) {
                $q->where('severity', 'critical')
                  ->orWhere('severity', 'high');
            })
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        // Bulk fetch incentive data for profiles too
        $incentiveValidDays = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as count'))
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('count', 'driver_id');

        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select('d.id', 'd.first_name', 'd.last_name', 'd.driver_status', 'u.plate_number')
            ->distinct('d.id')->get();

        $profiles = [];
        foreach ($drivers as $d) {
            $profiles[] = [
                'id'          => $d->id,
                'name'        => trim($d->first_name . ' ' . $d->last_name),
                'status'      => $d->driver_status,
                'unit'        => $d->plate_number,
                'incidents'   => $incidentCounts[$d->id] ?? 0,
                'shifts'      => $shiftCounts[$d->id] ?? 0,
                'charges'     => $chargesSum[$d->id] ?? 0,
                'violations'  => $violationCounts[$d->id] ?? 0,
                'total_debt'  => $debtSum[$d->id] ?? 0,
                'incentive'   => [
                    'valid_days' => $incentiveValidDays[$d->id] ?? 0,
                    'eligible'   => ($incentiveValidDays[$d->id] ?? 0) >= 20
                ],
            ];
        }

        return collect($profiles)->sortBy('name')->values();
    }

    private function computeIncentiveForDriver($driver_id, $unit)
    {
        $is_dual = $unit && !empty($unit->secondary_driver_id) && !empty($unit->driver_id);
        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        $violations = $this->getViolationCount(Driver::find($driver_id), null, null, true);
        $required_days = 20;
        $eligible = $valid_days >= $required_days && $violations === 0;

        $now = Carbon::now('Asia/Manila');
        $unitId = $unit->id ?? 0;

        if ($is_dual) {
            $isOddUnit = ($unitId % 2 !== 0);
            $currentMonth = $now->month;
            $isPayoutMonth = ($isOddUnit && ($currentMonth % 2 !== 0)) || (!$isOddUnit && ($currentMonth % 2 === 0));
            
            if ($isPayoutMonth) {
                $firstSunday = $now->copy()->startOfMonth();
                while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
                if ($now->gt($firstSunday->endOfDay())) {
                    $targetMonth = $now->copy()->addMonths(2)->startOfMonth();
                } else {
                    $targetMonth = $now->copy()->startOfMonth();
                }
            } else {
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            }
            if ($violations > 0) $targetMonth->addMonths(2);
        } else {
            $firstSunday = $now->copy()->startOfMonth();
            while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
            if ($now->gt($firstSunday->endOfDay())) {
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            } else {
                $targetMonth = $now->copy()->startOfMonth();
            }
            if ($violations > 0) $targetMonth->addMonth();
        }

        $payoutDate = $targetMonth->copy();
        while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) { $payoutDate->addDay(); }

        return [
            'is_dual'          => $is_dual,
            'valid_days'       => $valid_days,
            'violations'       => $violations,
            'eligible'         => $eligible,
            'next_payout_date' => $payoutDate->format('M d, Y'),
            'required_days'    => $required_days,
            'driver_type'      => $is_dual ? 'Dual Driver' : 'Solo Driver',
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'                => 'required|exists:units,id',
            'driver_id'              => 'required|exists:drivers,id',
            'incident_type'          => 'required|string',
            'severity'               => 'required|string',
            'description'            => 'required|string',
            'incident_date'          => 'nullable|date',
            'is_driver_fault'        => 'nullable|boolean',
            'sub_classification'     => 'nullable|string',
            'traffic_fine_amount'    => 'nullable|numeric|min:0',
            'total_charge_to_driver' => 'nullable|numeric|min:0',
        ]);

        $classification = IncidentClassification::where('name', $data['incident_type'])->first();
        $behaviorMode   = $classification?->behavior_mode ?? 'narrative';
        $isFault        = (bool)($data['is_driver_fault'] ?? false);
        $totalCharge    = (float)($data['total_charge_to_driver'] ?? 0);

        if ($behaviorMode === 'traffic' && !empty($data['traffic_fine_amount'])) {
            $totalCharge = (float)$data['traffic_fine_amount'];
        }

        $behavior = DriverBehavior::create([
            'unit_id'                 => $data['unit_id'],
            'driver_id'               => $data['driver_id'],
            'incident_type'           => $data['incident_type'],
            'sub_classification'      => $data['sub_classification'] ?? null,
            'traffic_fine_amount'     => $data['traffic_fine_amount'] ?? null,
            'severity'                => $data['severity'],
            'description'             => $data['description'],
            'is_driver_fault'         => $isFault,
            'total_charge_to_driver'  => $totalCharge,
            'total_paid'              => 0,
            'remaining_balance'       => $totalCharge,
            'charge_status'           => $totalCharge > 0 ? 'pending' : 'none',
            'timestamp'               => now()->timezone('Asia/Manila'),
            'incident_date'           => $data['incident_date'] ?? now()->timezone('Asia/Manila')->toDateString(),
        ]);

        // Void Incentive for the Day of Incident
        if ($behavior->isViolation() && !empty($data['incident_date'])) {
            DB::table('boundaries')
                ->where('driver_id', $data['driver_id'])
                ->whereDate('date', $data['incident_date'])
                ->update([
                    'has_incentive'         => false,
                    'counted_for_incentive' => false,
                    'notes'                 => DB::raw("CONCAT(COALESCE(notes,''), ' [Disqualified (Mobile): Recorded Violation - {$data['incident_type']}]')")
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Incident recorded successfully',
            'data'    => $behavior
        ]);
    }

    public function update(Request $request, $id)
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

        $finalCharge = (float)($data['total_charge_to_driver'] ?? 0);
        if ($data['incident_type'] === 'Traffic Violation' && !empty($data['traffic_fine_amount'])) {
            $finalCharge = (float)$data['traffic_fine_amount'];
        }

        $incident->update([
            'incident_type'          => $data['incident_type'],
            'severity'               => $data['severity'],
            'description'            => $data['description'],
            'incident_date'          => $data['incident_date'] ?? $incident->incident_date,
            'is_driver_fault'        => (bool)($data['is_driver_fault'] ?? false),
            'sub_classification'     => $data['sub_classification'] ?? null,
            'traffic_fine_amount'    => $data['traffic_fine_amount'] ?? $incident->traffic_fine_amount,
            'total_charge_to_driver' => $finalCharge,
            'remaining_balance'      => $finalCharge - $incident->total_paid,
            'charge_status'          => ($finalCharge - $incident->total_paid) > 0 ? 'pending' : 'none',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Incident record updated'
        ]);
    }

    public function destroy($id)
    {
        $incident = DriverBehavior::findOrFail($id);
        $incident->delete();

        return response()->json([
            'success' => true,
            'message' => 'Incident record archived'
        ]);
    }
}
