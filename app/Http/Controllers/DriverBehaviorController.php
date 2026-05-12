<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Http\Controllers\ActivityLogController;

use App\Models\Driver;
use App\Traits\CalculatesDriverPerformance;

class DriverBehaviorController extends Controller
{
    use CalculatesDriverPerformance;
    // Incident types are now managed in the database (IncidentClassification model)
    public static function getIncidentTypes()
    {
        return \App\Models\IncidentClassification::orderBy('name')->get();
    }

    // ─── INDEX: Unified Incident + Driver Dashboard ─────────────────────
    public function index(Request $request)
    {
        $search          = $request->input('search', '');
        $type_filter     = $request->input('type', '');
        $severity_filter = $request->input('severity', '');
        $date_from       = $request->input('date_from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $date_to         = $request->input('date_to', now()->timezone('Asia/Manila')->toDateString());
        $tab             = $request->input('tab', 'incidents');
        $page            = max(1, (int) $request->input('page', 1));
        $limit           = 10;
        $offset          = ($page - 1) * $limit;

        // ── Unified incident feed: driver_behavior with eager loading ──
        $query = \App\Models\DriverBehavior::query()
            ->with(['involvedParties', 'partsEstimates.part'])
            ->leftJoin('units as u', 'driver_behavior.unit_id', '=', 'u.id')
            ->leftJoin('drivers as d', 'driver_behavior.driver_id', '=', 'd.id')
            ->select(
                'driver_behavior.*',
                'u.plate_number',
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as driver_name"),
                DB::raw("'manual' as source")
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


        $pagination = [
            'page'        => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_items' => $total,
            'has_prev'    => $page > 1,
            'has_next'    => $page < ceil($total / $limit),
            'prev_page'   => $page - 1,
            'next_page'   => $page + 1,
        ];

        // ── Summary Stats ──────────────────────────────────────────────
        $stats = $this->getStats($date_from, $date_to);

        // ── Driver Performance Profiles ────────────────────────────────
        $driver_profiles = $this->getDriverProfiles($date_from, $date_to);

        // ── Incentive Eligibility ─────────────────────────────────────
        $incentive_summary = $this->getIncentiveSummary();

        // ── Dropdowns ─────────────────────────────────────────────────
        $drivers = DB::table('drivers as d')
            ->leftJoin('units as u', function($j) {
                $j->on('d.id', '=', 'u.driver_id')->orOn('d.id', '=', 'u.secondary_driver_id');
            })
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->select('d.id', 
                DB::raw("TRIM(CONCAT(COALESCE(d.first_name,''), ' ', COALESCE(d.last_name,''))) as full_name"),
                'u.plate_number as current_plate',
                'd.contact_number'
            )
            ->orderBy('d.last_name')->get();

        $units = DB::table('units')->whereNull('deleted_at')->where('status', '!=', 'retired')
            ->select('id', 'plate_number', 'driver_id', 'secondary_driver_id')
            ->orderBy('plate_number')->get();

        $spare_parts = \App\Models\SparePart::orderBy('name')->get();
        $classifications = \App\Models\IncidentClassification::orderBy('name')->get();
        $archivedClassifications = \App\Models\IncidentClassification::onlyTrashed()->orderBy('name')->get();

        return view('driver-behavior.index', compact(
            'incidents', 'search', 'type_filter', 'severity_filter',
            'date_from', 'date_to', 'pagination', 'stats',
            'driver_profiles', 'incentive_summary',
            'drivers', 'units', 'tab', 'spare_parts', 'classifications', 'archivedClassifications'
        ));
    }

    // ─── STORE: Record Incident ──────────────────────────────────────────
    public function store(Request $request)
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
            'missing_days_reported'  => 'nullable|integer|min:0|max:3650',
            'stolen_driver_detail_name' => 'nullable|string|max:255',
            'stolen_driver_detail_contact' => 'nullable|string|max:64',
            'stolen_driver_license_no' => 'nullable|string|max:64',
        ]);

        $parties = $request->input('parties', []);
        $parts   = $request->input('parts', []);
        $cause   = $request->input('cause_of_incident');

        // ── Resolve Classification metadata ────────────────────────────
        $classification = \App\Models\IncidentClassification::where('name', $data['incident_type'])->first();
        $behaviorMode   = $classification?->behavior_mode ?? 'narrative';

        $isFault    = (bool)($data['is_driver_fault'] ?? false);
        $isDamage   = $behaviorMode === 'damage';
        $isTraffic  = $behaviorMode === 'traffic';
        $isComplaint= $behaviorMode === 'complaint';
        $isSecurity = $behaviorMode === 'security';

        if ($isSecurity) {
            $secData = $request->validate([
                'missing_days_reported' => 'required|integer|min:0|max:3650',
                'stolen_driver_detail_name' => 'required|string|max:255',
                'stolen_driver_detail_contact' => 'nullable|string|max:64',
                'stolen_driver_license_no' => 'nullable|string|max:64',
            ]);
            $data['missing_days_reported'] = $secData['missing_days_reported'];
            $data['stolen_driver_detail_name'] = $secData['stolen_driver_detail_name'];
            $data['stolen_driver_detail_contact'] = $secData['stolen_driver_detail_contact'] ?? null;
            $data['stolen_driver_license_no'] = $secData['stolen_driver_license_no'] ?? null;
        } else {
            $data['missing_days_reported'] = null;
            $data['stolen_driver_detail_name'] = null;
            $data['stolen_driver_detail_contact'] = null;
            $data['stolen_driver_license_no'] = null;
        }

        // ── Compute damage costs only for damage mode ──────────────────
        $computedOwnUnitDamage = 0;
        $totalCharge = 0;

        if ($isDamage) {
            foreach ($parts as $partData) {
                // If it's a service (custom_part_name exists but no quantity), default qty to 1
                $qty   = (int)($partData['quantity'] ?? (isset($partData['custom_part_name']) ? 1 : 0));
                $price = (float)($partData['unit_price'] ?? 0);
                $isCharged = (bool)($partData['is_charged_to_driver'] ?? false);
                $itemTotal = $qty * $price;
                $computedOwnUnitDamage += $itemTotal;
                if ($isCharged) { $totalCharge += $itemTotal; }
            }
            if (count($parts) > 0) {
                $data['own_unit_damage_cost'] = $computedOwnUnitDamage;
            }
            if ($isFault) {
                $totalCharge += ($data['third_party_damage_cost'] ?? 0);
            }
        } elseif ($isTraffic) {
            // Traffic fine goes entirely to driver
            $totalCharge = (float)($data['traffic_fine_amount'] ?? 0);
        }
        // Complaint & narrative modes → no financial charge from incident form

        $data['total_charge_to_driver'] = $totalCharge;

        // ── Create main behavior record ────────────────────────────────
        $behavior = \App\Models\DriverBehavior::create([
            'unit_id'                 => $data['unit_id'],
            'driver_id'               => $data['driver_id'],
            'incident_type'           => $data['incident_type'],
            'sub_classification'      => $data['sub_classification'] ?? null,
            'traffic_fine_amount'     => $isTraffic ? ($data['traffic_fine_amount'] ?? 0) : null,
            'cause_of_incident'       => $cause,
            'severity'                => $data['severity'],
            'description'             => $data['description'],
            'third_party_name'        => $isDamage ? (collect($parties)->pluck('name')->filter()->implode(', ') ?: null) : null,
            'third_party_vehicle'     => $isDamage ? (collect($parties)->pluck('vehicle_type')->filter()->implode(', ') ?: null) : null,
            'own_unit_damage_cost'    => $data['own_unit_damage_cost'] ?? 0,
            'third_party_damage_cost' => $data['third_party_damage_cost'] ?? 0,
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
            'missing_days_reported'   => $data['missing_days_reported'] ?? null,
            'stolen_driver_detail_name' => $data['stolen_driver_detail_name'] ?? null,
            'stolen_driver_detail_contact' => $data['stolen_driver_detail_contact'] ?? null,
            'stolen_driver_license_no' => $data['stolen_driver_license_no'] ?? null,
        ]);

        // ── Insert Involved Parties (damage mode only) ─────────────────
        if ($isDamage) {
            foreach ($parties as $p) {
                if (!empty($p['name']) || !empty($p['plate_number'])) {
                    \App\Models\IncidentInvolvedParty::create([
                        'driver_behavior_id' => $behavior->id,
                        'name'               => $p['name'] ?? null,
                        'vehicle_type'       => $p['vehicle_type'] ?? null,
                        'plate_number'       => $p['plate_number'] ?? null,
                    ]);
                }
            }
            foreach ($parts as $partData) {
                $qty   = (int)($partData['quantity'] ?? (isset($partData['custom_part_name']) ? 1 : 0));
                $price = (float)($partData['unit_price'] ?? 0);
                if ($qty > 0 && $price >= 0) {
                    \App\Models\IncidentPartsEstimate::create([
                        'driver_behavior_id'   => $behavior->id,
                        'spare_part_id'        => !empty($partData['spare_part_id']) ? $partData['spare_part_id'] : null,
                        'custom_part_name'     => $partData['custom_part_name'] ?? null,
                        'quantity'             => $qty,
                        'unit_price'           => $price,
                        'total_price'          => $qty * $price,
                        'is_charged_to_driver' => (bool)($partData['is_charged_to_driver'] ?? false),
                    ]);
                }
            }
        }

        // ── Void Incentive for the Day of Incident ───────────
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

        // ── Auto-Ban & Lockdown Logic ─────────────────────────────────────────
        $shouldAutoBan = false;
        $isStolen      = false;

        // Trigger A: Specific keywords for "Taken / Stolen" OR Behavior Mode is 'security'
        if ($isSecurity || stripos($data['incident_type'], 'taken') !== false || stripos($data['incident_type'], 'stolen') !== false) {
            $isStolen = true;
            $shouldAutoBan = true; // Permanent ban for theft
        }
        
        // Trigger B: Specific sub-classification (e.g., 'Contracting') configured in settings
        if ($classification && $classification->auto_ban_trigger && !empty($data['sub_classification'])) {
            if ($data['sub_classification'] === $classification->ban_trigger_value) {
                $shouldAutoBan = true;
            }
        }

        // Trigger C: Manual override via Severity dropdown
        if (strtolower($data['severity']) === 'critical') {
            $shouldAutoBan = true;
        }

        if ($shouldAutoBan) {
            // Mark driver as banned
            DB::table('drivers')
                ->where('id', $data['driver_id'])
                ->update([
                    'driver_status' => 'banned',
                    'updated_at'    => now(),
                ]);

            // Automatically unassign banned driver from any units
            DB::table('units')
                ->where('driver_id', $data['driver_id'])
                ->update([
                    'driver_id' => null, 
                    'status' => 'active', // Ensure unit status stays active or resets if needed
                    'updated_at' => now()
                ]);
            DB::table('units')
                ->where('secondary_driver_id', $data['driver_id'])
                ->update([
                    'secondary_driver_id' => null,
                    'updated_at' => now()
                ]);

            if ($isStolen) {
                // Update unit status to missing
                DB::table('units')
                    ->where('id', $data['unit_id'])
                    ->update([
                        'status' => 'missing',
                        'updated_at' => now()
                    ]);
            }

            // Log a system alert
            $driver    = DB::table('drivers')->find($data['driver_id']);
            $unit      = DB::table('units')->find($data['unit_id']);
            $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
            
            $alertTitle = $isStolen ? "🚨 STOLEN/TAKEN VEHICLE: {$unit->plate_number}" : "🚫 AUTO-BAN: {$driverName}";
            $alertMsg   = $isStolen ? "CRITICAL: Unit {$unit->plate_number} has been reported as TAKEN/STOLEN by {$driverName}. Vehicle is now in LOCKDOWN (Missing status)." : "Driver {$driverName} has been automatically banned due to a Contracting / passenger complaint violation on unit {$unit->plate_number}.";

            DB::table('system_alerts')->insert([
                'title'       => $alertTitle,
                'message'     => $alertMsg,
                'type'        => 'danger',
                'is_resolved' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // ── Create system alert for accidents/damage ───────────────────
        if ($isDamage) {
            $unit      = DB::table('units')->find($data['unit_id']);
            $driver    = DB::table('drivers')->find($data['driver_id']);
            $plateName  = $unit->plate_number ?? 'Unknown Unit';
            $driverName = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));
            DB::table('system_alerts')->insert([
                'title'       => "Accident Reported: {$plateName}",
                'message'     => "Driver {$driverName} reported an accident. Fault: " . ($isFault ? 'YES' : 'NO') . ". Charge: ₱" . number_format($totalCharge, 2),
                'type'        => 'danger',
                'is_resolved' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $driverName = DB::table('drivers')->where('id', $data['driver_id'])->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
        $plate = DB::table('units')->where('id', $data['unit_id'])->value('plate_number');
        ActivityLogController::log('Recorded Incident', "Driver: {$driverName}\nUnit: {$plate}\nType: {$data['incident_type']}\nSeverity: " . ucfirst($data['severity']));
        
        // Notify Driver via Push
        try {
            $chargeMsg = $totalCharge > 0 ? " and a charge of ₱" . number_format($totalCharge, 2) . " was added." : ".";
            app(\App\Services\NotificationService::class)->notifyDriver(
                $data['driver_id'],
                'Incident Recorded: ' . $data['incident_type'],
                "A new " . strtolower($data['severity']) . " severity incident was recorded" . $chargeMsg,
                'incident'
            );
        } catch (\Exception $e) {}

        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident recorded successfully.' . ($shouldAutoBan ? ' Driver has been automatically BANNED due to contracting violation.' : ''));
    }

    // ─── SHOW: Get Incident Details (JSON) ──────────────────────────────
    public function show($id)
    {
        try {
            $incident = \App\Models\DriverBehavior::with(['driver', 'unit', 'involvedParties', 'partsEstimates'])
                ->findOrFail($id);
            
            // Map for JS compatibility
            $incident->driver_name  = trim(($incident->driver->first_name ?? '') . ' ' . ($incident->driver->last_name ?? ''));
            $incident->plate_number = $incident->unit->plate_number ?? 'N/A';
            
            return response()->json($incident);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Incident not found'], 404);
        }
    }

    // ─── UPDATE: Update Incident ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $incident = \App\Models\DriverBehavior::findOrFail($id);
        
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

        $prevFault = $incident->is_driver_fault;
        $prevDate  = $incident->incident_date;

        $isFault = (bool)($data['is_driver_fault'] ?? false);
        
        // If it's a traffic violation and total_charge is not explicitly provided (hidden in UI), use fine amount
        $finalCharge = $data['total_charge_to_driver'] ?? 0;
        if ($data['incident_type'] === 'Traffic Violation' && !empty($data['traffic_fine_amount'])) {
            $finalCharge = $data['traffic_fine_amount'];
        }

        $incident->update([
            'incident_type'          => $data['incident_type'],
            'severity'               => $data['severity'],
            'description'            => $data['description'],
            'is_driver_fault'        => $isFault,
            'sub_classification'     => $data['sub_classification'] ?? $incident->sub_classification,
            'traffic_fine_amount'    => $data['traffic_fine_amount'] ?? 0,
            'total_charge_to_driver' => $finalCharge,
            'remaining_balance'      => $finalCharge, // Reset for simplicity in this edit flow
            'incident_date'          => $data['incident_date'] ?? $incident->incident_date,
        ]);

        // Logic check: If fault changed or date changed, update boundaries
        if ($isFault != $prevFault || $incident->incident_date != $prevDate) {
            // Restore previous date incentive if it was fault and now not
            if ($prevFault) {
                DB::table('boundaries')
                    ->where('driver_id', $incident->driver_id)
                    ->whereDate('date', $prevDate)
                    ->update(['has_incentive' => true, 'counted_for_incentive' => true]);
            }
            
            // Void new date if it's now fault
            if ($isFault) {
                DB::table('boundaries')
                    ->where('driver_id', $incident->driver_id)
                    ->whereDate('date', $incident->incident_date)
                    ->update(['has_incentive' => false, 'counted_for_incentive' => false]);
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Incident updated successfully.']);
        }

        $driverName = DB::table('drivers')->where('id', $incident->driver_id)->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
        ActivityLogController::log('Updated Incident', "Incident #{$id} ({$incident->incident_type})\nDriver: {$driverName}\nSeverity changed to: " . ucfirst($incident->severity));

        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident updated successfully.');
    }

    // ─── DELETE (SOFT DELETE TO ARCHIVE) ───────────────────────────────
    public function destroy($id)
    {
        $behavior = \App\Models\DriverBehavior::findOrFail($id);
        $type = $behavior->incident_type;
        $driverId = $behavior->driver_id;
        $behavior->delete();
        
        $driverName = DB::table('drivers')->where('id', $driverId)->select(DB::raw("CONCAT(first_name, ' ', last_name) as name"))->value('name');
        ActivityLogController::log('Archived Incident', "Type: {$type}\nDriver: {$driverName}\nRecord moved to archive.");

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Incident moved to Archive.']);
        }
        
        return redirect()->route('driver-behavior.index', ['tab' => 'incidents'])
            ->with('success', 'Incident moved to Archive.');
    }

    // ─── RELEASE INCENTIVE ───────────────────────────────────────────────
    public function releaseIncentive(Request $request)
    {
        $driver_id   = $request->input('driver_id');
        $release_date = now()->timezone('Asia/Manila')->toDateString();

        // Mark ALL unreleased boundaries for this driver as released (Clears shortages/late/absent)
        DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->update(['incentive_released_at' => $release_date]);

        // Mark ALL violations for this driver as released (Clears traffic/damage/accidents)
        DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->whereNull('incentive_released_at')
            ->update(['incentive_released_at' => $release_date]);

        $driver = DB::table('drivers')->find($driver_id);
        $name = trim(($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''));

        ActivityLogController::log('Released Incentive', "Driver: {$name}\nAll unreleased boundaries and violations have been cleared.");

        return redirect()->route('driver-behavior.index', ['tab' => 'incentives'])
            ->with('success', "Incentive released for {$name}. Counter reset.");
    }

    // ─── DRIVER PERFORMANCE JSON ─────────────────────────────────────────
    public function getDriverPerformance(Request $request, $driver_id)
    {
        $from = $request->input('from', now()->timezone('Asia/Manila')->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->timezone('Asia/Manila')->toDateString());

        $driver = DB::table('drivers')->find($driver_id);
        if (!$driver) return response()->json(['error' => 'Driver not found'], 404);

        $unit = DB::table('units')
            ->where(function($q) use ($driver_id) {
                $q->where('driver_id', $driver_id)
                  ->orWhere('secondary_driver_id', $driver_id);
            })
            ->whereNull('deleted_at')
            ->first();

        $incidents = DB::table('driver_behavior as db')
            ->leftJoin('units as u', 'db.unit_id', '=', 'u.id')
            ->where('db.driver_id', $driver_id)
            ->whereDate('db.timestamp', '>=', $from)
            ->whereDate('db.timestamp', '<=', $to)
            ->select('db.*', 'u.plate_number')
            ->orderByDesc('db.timestamp')
            ->limit(10)->get();

        $boundaries_count = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->count();

        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        $totalCharges = DB::table('driver_behavior')
            ->where('driver_id', $driver_id)
            ->sum('total_charge_to_driver');

        $incentive = $this->computeIncentiveForDriver($driver_id, $unit);

        return response()->json([
            'driver'           => $driver,
            'unit'             => $unit,
            'incidents'        => $incidents,
            'boundaries_count' => $boundaries_count,
            'valid_days'       => $valid_days,
            'total_charges'    => $totalCharges,
            'incentive'        => $incentive,
        ]);
    }

    // ─── PRIVATE: Compute Incentive For One Driver ───────────────────────
    private function computeIncentiveForDriver($driver_id, $unit)
    {
        // Determine if solo or dual
        $is_dual = $unit && !empty($unit->secondary_driver_id) && !empty($unit->driver_id);

        // Count unreleased valid boundary days
        $valid_days = DB::table('boundaries')
            ->where('driver_id', $driver_id)
            ->where('counted_for_incentive', true)
            ->where('has_incentive', true)
            ->whereNull('incentive_released_at')
            ->count();

        // Violations this period (Unified Behavioral + Boundary Verification)
        $violations = $this->getViolationCount(Driver::find($driver_id), null, null, true);

        $required_days = 20;
        $eligible = $valid_days >= $required_days && $violations === 0;

        // Compute next payout Sunday
        $now = Carbon::now('Asia/Manila');
        
        // Find the unit ID for staggering dual drivers
        $unitId = $unit->id ?? 0;

        if ($is_dual) {
            // Dual Driver: 2-month cycle staggered by Unit ID.
            // Split dual drivers into 2 groups so they don't all pay on the same month.
            // Group A (Odd Unit ID): Pays in ODD months (Jan, Mar, May, Jul, Sep, Nov)
            // Group B (Even Unit ID): Pays in EVEN months (Feb, Apr, Jun, Aug, Oct, Dec)
            
            $isOddUnit = ($unitId % 2 !== 0);
            $currentMonth = $now->month;
            
            // Determine if THIS month is the payout month for this unit
            $isPayoutMonth = ($isOddUnit && ($currentMonth % 2 !== 0)) || (!$isOddUnit && ($currentMonth % 2 === 0));
            
            if ($isPayoutMonth) {
                // Check if the 1st Sunday of THIS month has already passed
                $firstSunday = $now->copy()->startOfMonth();
                while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
                
                if ($now->gt($firstSunday->endOfDay())) {
                    // Passed -> Next natural payout is in 2 months
                    $targetMonth = $now->copy()->addMonths(2)->startOfMonth();
                } else {
                    // Not passed -> Natural payout is THIS month
                    $targetMonth = $now->copy()->startOfMonth();
                }
            } else {
                // Not the payout month -> Payout is NEXT month
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more 2-month cycle
            if ($violations > 0) {
                $targetMonth->addMonths(2);
            }

        } else {
            // Solo Driver: Strictly 1 month cycle. Payout is every 1st Sunday.
            $firstSunday = $now->copy()->startOfMonth();
            while ($firstSunday->dayOfWeek !== Carbon::SUNDAY) { $firstSunday->addDay(); }
            
            if ($now->gt($firstSunday->endOfDay())) {
                $targetMonth = $now->copy()->addMonth()->startOfMonth();
            } else {
                $targetMonth = $now->copy()->startOfMonth();
            }

            // [NEW] Skip logic: If there are violations, jump one more month
            if ($violations > 0) {
                $targetMonth->addMonth();
            }
        }

        // Find the 1st Sunday of the target month
        $payoutDate = $targetMonth->copy();
        while ($payoutDate->dayOfWeek !== Carbon::SUNDAY) {
            $payoutDate->addDay();
        }

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

    // ─── PRIVATE: Summary Stats ─────────────────────────────────────────
    private function getStats($from, $to)
    {
        $base  = DB::table('driver_behavior')->whereDate('timestamp', '>=', $from)->whereDate('timestamp', '<=', $to);
        $bySev = (clone $base)->selectRaw('severity, COUNT(*) as count')->groupBy('severity')->get()->pluck('count', 'severity')->toArray();
        $byType = (clone $base)->selectRaw('incident_type, COUNT(*) as count')->groupBy('incident_type')->orderByDesc('count')->limit(8)->get();

        $totalViolators = DB::table('driver_behavior')
            ->whereDate('timestamp', '>=', $from)
            ->whereDate('timestamp', '<=', $to)
            ->distinct('driver_id')
            ->count('driver_id');

        $totalCharges   = DB::table('driver_behavior')->sum('total_charge_to_driver');
        $pendingCharges = DB::table('driver_behavior')->where('charge_status', 'pending')->sum('total_charge_to_driver');
        
        $violationsToday = DB::table('driver_behavior')
            ->whereDate('timestamp', now()->format('Y-m-d'))
            ->count();

        return [
            'incidents_period'  => (clone $base)->count(),
            'violations_today'  => $violationsToday,
            'by_severity'       => $bySev,
            'incident_types'    => $byType,
            'total_violators'   => $totalViolators,
            'total_charges'     => $totalCharges,
            'pending_charges'   => $pendingCharges,
        ];
    }

    // ─── PRIVATE: Driver Profiles ───────────────────────────────────────
    private function getDriverProfiles($from, $to)
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select(
                'd.id', 'd.first_name', 'd.last_name', 'd.driver_status',
                'u.id as unit_id', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id'
            )
            ->distinct('d.id')
            ->get();

        // ── OPTIMIZATION: Bulk fetch statistics to avoid N+1 queries ──
        $incidentCounts = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $debtSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(remaining_balance) as aggregate'))
            ->where('charge_status', 'pending')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $boundaryCounts = DB::table('boundaries')
            ->select('driver_id', DB::raw('count(*) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $shortageSum = DB::table('boundaries')
            ->select('driver_id', DB::raw('sum(shortage) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $chargeSum = DB::table('driver_behavior')
            ->select('driver_id', DB::raw('sum(total_charge_to_driver) as aggregate'))
            ->whereNull('incentive_released_at')
            ->groupBy('driver_id')
            ->pluck('aggregate', 'driver_id');

        $profiles = [];
        foreach ($drivers as $d) {
            $unit_obj  = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null, 'plate_number' => $d->plate_number ?? null, 'id' => $d->unit_id];
            
            // We still need this for complex date logic, but the statistics are now fast.
            $incentive = $this->computeIncentiveForDriver($d->id, $unit_obj);

            $profiles[] = [
                'id'          => $d->id,
                'name'        => trim($d->first_name . ' ' . $d->last_name),
                'status'      => $d->driver_status,
                'unit'        => $d->plate_number,
                'unit_id'     => $d->unit_id,
                'incidents'   => $incidentCounts[$d->id] ?? 0,
                'boundaries'  => $boundaryCounts[$d->id] ?? 0,
                'shortages'   => $shortageSum[$d->id] ?? 0,
                'charges'     => $chargeSum[$d->id] ?? 0,
                'total_debt'  => $debtSum[$d->id] ?? 0,
                'incentive'   => $incentive,
            ];
        }

        return collect($profiles)->sortBy('name')->values();
    }

    // ─── PRIVATE: Incentive Summary ─────────────────────────────────────
    private function getIncentiveSummary()
    {
        $drivers = DB::table('drivers as d')
            ->whereNull('d.deleted_at')
            ->where('d.driver_status', '!=', 'banned')
            ->leftJoin('units as u', function($j) {
                $j->on('u.driver_id', '=', 'd.id')->orOn('u.secondary_driver_id', '=', 'd.id');
            })
            ->whereNull('u.deleted_at')
            ->select('d.id', 'd.first_name', 'd.last_name', 'u.plate_number', 'u.driver_id', 'u.secondary_driver_id')
            ->distinct('d.id')->get();

        $eligible   = [];
        $ineligible = [];

        foreach ($drivers as $d) {
            $unit = (object)['driver_id' => $d->driver_id ?? null, 'secondary_driver_id' => $d->secondary_driver_id ?? null];
            
            // This method is still called in a loop, but computeIncentiveForDriver 
            // is the core business logic. The previous optimization in getDriverProfiles 
            // addressed the most egregious N+1. Here we compute the full incentive status.
            $inc = $this->computeIncentiveForDriver($d->id, $unit);

            $row = [
                'driver_id'     => $d->id,
                'name'          => trim($d->first_name . ' ' . $d->last_name),
                'unit'          => $d->plate_number,
                'driver_type'   => $inc['driver_type'],
                'valid_days'    => $inc['valid_days'],
                'violations'    => $inc['violations'],
                'eligible'      => $inc['eligible'],
                'next_payout'   => $inc['next_payout_date'],
            ];

            if ($inc['eligible']) {
                $eligible[] = $row;
            } else {
                $ineligible[] = $row;
            }
        }

        return ['eligible' => $eligible, 'ineligible' => $ineligible];
    }

    public function getStatistics(Request $request)
    {
        // Kept for backward compatibility
        return response()->json($this->getStats(now()->subDays(30)->toDateString(), now()->toDateString()));
    }
}
