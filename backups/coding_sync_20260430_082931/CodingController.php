<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodingController extends Controller
{
    public function index(Request $request)
    {
        $page = (int)($request->input('page', 1));
        $search = $request->input('search', '');
        $date = $request->input('date', date('Y-m-d'));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = DB::table('coding_rules as cr')
            ->select('cr.*');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('cr.coding_day', 'like', "%{$search}%")
                    ->orWhere('cr.restricted_plate_numbers', 'like', "%{$search}%")
                    ->orWhere('cr.notes', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $rules = $query->orderBy('cr.coding_day', 'asc')->orderBy('cr.status', 'desc')->offset($offset)->limit($limit)->get();

        $pagination = [
            'page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total / $limit),
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // Get units for dropdown
        $units = DB::table('units')
            ->select('id', 'plate_number', 'coding_day', 'make', 'model', 'status')
            ->orderBy('plate_number')
            ->get();

        // Get today's coding status
        $today_name = now()->timezone('Asia/Manila')->format('l');
        
        // 1. Get the FULL fleet for the calendar and overall stats (unfiltered)
        $full_fleet = DB::table('units as u')
            ->leftJoin('drivers as drv1', 'u.driver_id', '=', 'drv1.id')
            ->leftJoin('drivers as drv2', 'u.secondary_driver_id', '=', 'drv2.id')
            ->select(
                'u.*', 
                DB::raw("CONCAT(COALESCE(drv1.first_name,''), ' ', COALESCE(drv1.last_name,'')) as driver1_name"),
                DB::raw("CONCAT(COALESCE(drv2.first_name,''), ' ', COALESCE(drv2.last_name,'')) as driver2_name")
            )
            ->get();

        foreach ($full_fleet as $u) {
            if (empty($u->coding_day)) {
                $u->coding_day = $this->deriveCodingDay($u->plate_number);
            }
        }

        // 2. Handle Search for the Table specifically
        $today_units = $full_fleet->filter(function($u) use ($today_name, $search) {
            // Filter by coding day first
            $is_coding_today = $u->coding_day === $today_name;
            
            // If there's a search query, only keep those that match the plate number
            if (!empty($search)) {
                return $is_coding_today && stripos($u->plate_number, $search) !== false;
            }
            
            return $is_coding_today;
        });

        // Metrics calculation for the "Today's Focus" section (Always based on FULL fleet)
        $total_fleet_count = $full_fleet->where('status', '!=', 'Inactive')->count();
        $coding_today_count = $full_fleet->where('coding_day', $today_name)->count();
        $on_road_count = max(0, $total_fleet_count - $coding_today_count);
        $violations_count = $full_fleet->where('coding_day', $today_name)->where('status', 'Available')->count();

        // Get coding statistics
        $stats = [
            'total_rules' => DB::table('coding_rules')->count(),
            'active_rules' => DB::table('coding_rules')->where('status', 'active')->count(),
            'today_coding' => $coding_today_count,
            'on_road' => $on_road_count,
            'violations' => $violations_count,
            'today_violators' => DB::table('coding_violations')
                ->whereDate('violation_time', now()->timezone('Asia/Manila')->toDateString())
                ->distinct('unit_id')
                ->count('unit_id'),
        ];

        // Build coding calendar (Always based on FULL fleet)
        $coding_calendar = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        
        foreach ($days as $day) {
            $coding_calendar[$day] = $full_fleet->filter(function($u) use ($day) {
                return $u->coding_day === $day;
            });
        }

        return view('coding.index', compact('rules', 'pagination', 'search', 'date', 'units', 'today_units', 'today_name', 'stats', 'coding_calendar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'coding_day' => 'required|string',
            'restricted_plate_numbers' => 'required|string',
            'coding_type' => 'required|string|in:full_ban,partial',
            'allowed_areas' => 'nullable|string',
            'time_start' => 'nullable|string',
            'time_end' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        if ($data['coding_day'] === '' || $data['restricted_plate_numbers'] === '') {
            return back()->with('error', 'Please fill in Coding Day and Restricted Plate Numbers.');
        }

        if (!in_array($data['coding_day'], ['Monday','Tuesday','Wednesday','Thursday','Friday'], true)) {
            return back()->with('error', 'Invalid Coding Day.');
        }

        if (!in_array($data['coding_type'], ['full_ban', 'partial'], true)) {
            $data['coding_type'] = 'full_ban';
        }

        if (!in_array($data['status'], ['active', 'inactive'], true)) {
            $data['status'] = 'active';
        }

        $time_start_db = $data['time_start'] !== '' ? $data['time_start'] : null;
        $time_end_db = $data['time_end'] !== '' ? $data['time_end'] : null;

        DB::table('coding_rules')->insert([
            'coding_day' => $data['coding_day'],
            'restricted_plate_numbers' => $data['restricted_plate_numbers'],
            'coding_type' => $data['coding_type'],
            'allowed_areas' => $data['allowed_areas'],
            'time_start' => $time_start_db,
            'time_end' => $time_end_db,
            'notes' => $data['notes'],
            'status' => $data['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('coding.index')->with('success', 'Coding rule added successfully');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'coding_day' => 'required|string',
            'restricted_plate_numbers' => 'required|string',
            'coding_type' => 'required|string|in:full_ban,partial',
            'allowed_areas' => 'nullable|string',
            'time_start' => 'nullable|string',
            'time_end' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $time_start_db = $data['time_start'] !== '' ? $data['time_start'] : null;
        $time_end_db = $data['time_end'] !== '' ? $data['time_end'] : null;

        DB::table('coding_rules')->where('id', $id)->update([
            'coding_day' => $data['coding_day'],
            'restricted_plate_numbers' => $data['restricted_plate_numbers'],
            'coding_type' => $data['coding_type'],
            'allowed_areas' => $data['allowed_areas'],
            'time_start' => $time_start_db,
            'time_end' => $time_end_db,
            'notes' => $data['notes'],
            'status' => $data['status'],
            'updated_at' => now(),
        ]);

        return redirect()->route('coding.index')->with('success', 'Coding rule updated successfully');
    }

    public function destroy($id)
    {
        DB::table('coding_rules')->where('id', $id)->delete();
        return redirect()->route('coding.index')->with('success', 'Coding rule deleted successfully');
    }

    public function updateCodingDay(Request $request)
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

        return redirect()->route('coding.index')->with('success', 'Coding day updated successfully');
    }

    public function violations(Request $request)
    {
        $page = (int)($request->input('page', 1));
        $date = $request->input('date');
        $search = $request->input('search');
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $query = DB::table('coding_violations as cv')
            ->join('units as u', 'cv.unit_id', '=', 'u.id')
            ->select('cv.*', 'u.plate_number', 'u.make', 'u.model');

        if (!empty($date)) {
            $query->whereDate('cv.violation_time', $date);
        }

        if (!empty($search)) {
            $query->where('u.plate_number', 'like', "%{$search}%");
        }

        $query->orderByDesc('cv.violation_time');

        $total = $query->count();
        $violations = $query->offset($offset)->limit($limit)->get();

        $pagination = [
            'page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total / $limit),
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        return view('coding.violations', compact('violations', 'pagination', 'date', 'search'));
    }

    public function suggestions(Request $request)
    {
        $q = $request->input('q');
        if (empty($q)) return response()->json([]);

        $units = DB::table('units')
            ->where('plate_number', 'like', "%{$q}%")
            ->select('plate_number', 'coding_day')
            ->limit(10)
            ->get();

        foreach ($units as $u) {
            if (empty($u->coding_day)) {
                $u->coding_day = $this->deriveCodingDay($u->plate_number);
            }
        }

        return response()->json($units);
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
}
