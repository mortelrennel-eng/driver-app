<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', '');
        $type = $request->input('type', '');
        $page = max(1, (int) $request->input('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $query = DB::table('maintenance')
            ->whereNull('maintenance.deleted_at')
            ->join('units', 'maintenance.unit_id', '=', 'units.id')
            ->whereNull('units.deleted_at')
            ->leftJoin('drivers', 'maintenance.driver_id', '=', 'drivers.id')
            ->leftJoin('users as creator', 'maintenance.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'maintenance.updated_by', '=', 'editor.id')
            ->select(
                'maintenance.*',
                DB::raw('maintenance.id as id'),  // explicit — prevents collision with joined table ids
                'units.plate_number', 
                DB::raw('CONCAT(drivers.first_name, " ", drivers.last_name) as driver_name'),
                'creator.full_name as creator_name', 
                'editor.full_name as editor_name'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('units.plate_number', 'like', "%{$search}%")
                    ->orWhere('maintenance.description', 'like', "%{$search}%")
                    ->orWhere('maintenance.mechanic_name', 'like', "%{$search}%")
                    ->orWhere(DB::raw('CONCAT(drivers.first_name, " ", drivers.last_name)'), 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('maintenance.status', $status);
        }

        if ($type) {
            $query->where('maintenance.maintenance_type', $type);
        }

        $total = $query->count();
        $records = $query->orderByDesc('maintenance.date_started')->offset($offset)->limit($limit)->get();

        $total_pages = max(1, ceil($total / $limit));

        $totals = DB::table('maintenance')->whereNull('deleted_at')->selectRaw('
            COUNT(*) as total_count,
            SUM(cost) as total_cost,
            SUM(CASE WHEN maintenance.status = "completed" THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN maintenance.status = "pending" THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN maintenance.status IN ("in_progress", "in_shop", "testing") THEN 1 ELSE 0 END) as in_progress_count
        ')->first();

        // 7-Day Trend Data for Sparklines
        $sevenDaysAgo = date('Y-m-d', strtotime('-6 days'));
        $trendsRaw = DB::table('maintenance')
            ->whereNull('deleted_at')
            ->where('date_started', '>=', $sevenDaysAgo)
            ->selectRaw('
                DATE(date_started) as d,
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status IN ("in_progress", "in_shop", "testing") THEN 1 ELSE 0 END) as active,
                SUM(cost) as cost
            ')
            ->groupBy(DB::raw('DATE(date_started)'))
            ->get()
            ->keyBy('d');

        $trends = ['total' => [], 'pending' => [], 'active' => [], 'cost' => []];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $trends['total'][] = $trendsRaw->has($d) ? $trendsRaw[$d]->total : 0;
            $trends['pending'][] = $trendsRaw->has($d) ? $trendsRaw[$d]->pending : 0;
            $trends['active'][] = $trendsRaw->has($d) ? $trendsRaw[$d]->active : 0;
            $trends['cost'][] = $trendsRaw->has($d) ? $trendsRaw[$d]->cost : 0;
        }

        $units = DB::table('units')->whereNull('deleted_at')->where('status', '!=', 'retired')->orderBy('plate_number')->get();
        $drivers = DB::table('drivers')
            ->whereNull('deleted_at')
            ->select('id', DB::raw('CONCAT(first_name, " ", last_name) as name'), 'nickname')
            ->orderBy('first_name')
            ->get();
        $staff = DB::table('staff')->whereNull('deleted_at')->where('role', 'Mechanic')->orderBy('name')->get();
        $spare_parts = DB::table('spare_parts')->orderBy('name')->get();
        $suppliers = DB::table('suppliers')->whereNull('deleted_at')->orderBy('name')->get();

        // Stock Purchase History from Expenses
        $purchaseHistory = DB::table('expenses')
            ->where('category', 'maintenance')
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $pagination = [
            'page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages,
            'prev_page' => $page - 1,
            'next_page' => $page + 1,
        ];

        // MaintNotifs is now handled globally in AppServiceProvider

        return view('maintenance.index', compact(
            'records',
            'search',
            'status',
            'type',
            'pagination',
            'totals',
            'units',
            'drivers',
            'staff',
            'spare_parts',
            'suppliers',
            'purchaseHistory',
            'trends'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|integer',
            'driver_id' => 'nullable|integer',
            'maintenance_type' => 'required|string',
            'description' => 'nullable|string',
            'dispatcher_notes' => 'nullable|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer',
            'date_started' => 'required|date',
            'date_completed' => 'nullable|date',
            'status' => 'required|string',
            'mechanic_name' => 'required|array',
            'mechanic_name.*' => 'nullable|string',
            'parts_list' => 'nullable|string',
            'parts_data' => 'nullable|string', // JSON from UI
            'cost' => 'required|numeric|min:0',
        ]);

        // Combine mechanic names
        $mechs = array_filter($data['mechanic_name']);
        $data['mechanic_name'] = implode(', ', $mechs);

        if (!empty($data['dispatcher_notes'])) {
            $data['description'] .= "\n\nDispatcher Notes:\n" . trim($data['dispatcher_notes']);
        }
        unset($data['dispatcher_notes']);

        // Process parts and other costs if provided
        if (!empty($data['parts_data'])) {
            $parsed = json_decode($data['parts_data'], true);
            if (is_array($parsed)) {
                $summary = [];
                if (!empty($parsed['parts'])) {
                    foreach ($parsed['parts'] as $p) {
                        $summary[] = ($p['name'] ?? 'Part') . " (x" . ($p['qty'] ?? 1) . ")";
                    }
                }
                if (!empty($parsed['others'])) {
                    foreach ($parsed['others'] as $o) {
                        if (!empty($o['name'])) $summary[] = $o['name'];
                    }
                }
                $data['parts_list'] = implode(', ', $summary);
            }
        }

        // Update unit status based on maintenance completion
        if ($data['status'] === 'completed' && $data['date_completed']) {
            DB::table('units')->where('id', $data['unit_id'])->update(['status' => 'active', 'updated_at' => now()]);
        } else if (in_array($data['status'], ['pending', 'in_progress', 'in_shop', 'testing'])) {
            DB::table('units')->where('id', $data['unit_id'])->update(['status' => 'maintenance', 'updated_at' => now()]);
        }

        // Use Eloquent to trigger TrackChanges trait
        return DB::transaction(function () use ($data, $parsed) {
            $maintenance = Maintenance::create($data);

            // Store individual parts for history
            if (!empty($data['parts_data']) && is_array($parsed)) {
                // Store Spare Parts
                if (!empty($parsed['parts'])) {
                    foreach ($parsed['parts'] as $p) {
                        DB::table('maintenance_parts')->insert([
                            'maintenance_id' => $maintenance->id,
                            'part_id' => $p['id'] ?? null,
                            'part_name' => $p['name'] ?? 'Part',
                            'quantity' => $p['qty'] ?? 1,
                            'price' => $p['price'] ?? 0,
                            'total' => ($p['price'] ?? 0) * ($p['qty'] ?? 1),
                            'created_at' => now(), 'updated_at' => now()
                        ]);

                        // DECREMENT STOCK
                        if (!empty($p['id'])) {
                            DB::table('spare_parts')->where('id', $p['id'])->decrement('stock_quantity', $p['qty'] ?? 1);
                        }
                    }
                }
                // Store Other Costs/Services
                if (!empty($parsed['others'])) {
                    foreach ($parsed['others'] as $o) {
                        if (empty($o['name'])) continue;
                        DB::table('maintenance_parts')->insert([
                            'maintenance_id' => $maintenance->id,
                            'part_id' => null,
                            'part_name' => $o['name'],
                            'quantity' => 1,
                            'price' => $o['price'] ?? 0,
                            'total' => $o['price'] ?? 0,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }
                }
            }

            return redirect()->route('maintenance.index')->with('success', 'Maintenance record added successfully');
        });
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'unit_id' => 'required|integer',
            'driver_id' => 'nullable|integer',
            'maintenance_type' => 'required|string',
            'description' => 'nullable|string',
            'dispatcher_notes' => 'nullable|string',
            'labor_cost' => 'nullable|numeric|min:0',
            'odometer_reading' => 'nullable|integer',
            'date_started' => 'required|date',
            'date_completed' => 'nullable|date',
            'status' => 'required|string',
            'mechanic_name' => 'required|array',
            'mechanic_name.*' => 'nullable|string',
            'parts_list' => 'nullable|string',
            'parts_data' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
        ]);

        // Combine mechanic names
        $mechs = array_filter($data['mechanic_name']);
        $data['mechanic_name'] = implode(', ', $mechs);

        if (!empty($data['dispatcher_notes'])) {
            $data['description'] .= "\n\nDispatcher Notes:\n" . trim($data['dispatcher_notes']);
        }
        unset($data['dispatcher_notes']);

        // Process parts if provided
        if (!empty($data['parts_data'])) {
            $parsed = json_decode($data['parts_data'], true);
            if (is_array($parsed)) {
                $summary = [];
                if (!empty($parsed['parts'])) {
                    foreach ($parsed['parts'] as $p) {
                        $summary[] = ($p['name'] ?? 'Part') . " (x" . ($p['qty'] ?? 1) . ")";
                    }
                }
                if (!empty($parsed['others'])) {
                    foreach ($parsed['others'] as $o) {
                        if (!empty($o['name'])) $summary[] = $o['name'];
                    }
                }
                $data['parts_list'] = implode(', ', $summary);
            }
        }

        // Use Eloquent to trigger TrackChanges trait
        return DB::transaction(function () use ($id, $data, $parsed) {
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->update($data);

            // Update unit status based on maintenance completion
            if ($data['status'] === 'completed' && $data['date_completed']) {
                DB::table('units')->where('id', $data['unit_id'])->update(['status' => 'active', 'updated_at' => now()]);
            } else if (in_array($data['status'], ['pending', 'in_progress', 'in_shop', 'testing'])) {
                DB::table('units')->where('id', $data['unit_id'])->update(['status' => 'maintenance', 'updated_at' => now()]);
            }

            // Update individual parts history
            if (!empty($data['parts_data']) && is_array($parsed)) {
                // RETURN OLD STOCK FIRST
                $oldParts = DB::table('maintenance_parts')->where('maintenance_id', $id)->get();
                foreach ($oldParts as $old) {
                    if ($old->part_id) {
                        DB::table('spare_parts')->where('id', $old->part_id)->increment('stock_quantity', $old->quantity);
                    }
                }

                DB::table('maintenance_parts')->where('maintenance_id', $id)->delete();
                
                if (!empty($parsed['parts'])) {
                    foreach ($parsed['parts'] as $p) {
                        DB::table('maintenance_parts')->insert([
                            'maintenance_id' => $maintenance->id,
                            'part_id' => $p['id'] ?? null,
                            'part_name' => $p['name'] ?? 'Part',
                            'quantity' => $p['qty'] ?? 1,
                            'price' => $p['price'] ?? 0,
                            'total' => ($p['price'] ?? 0) * ($p['qty'] ?? 1),
                            'created_at' => now(), 'updated_at' => now()
                        ]);

                        // DECREMENT NEW STOCK
                        if (!empty($p['id'])) {
                            DB::table('spare_parts')->where('id', $p['id'])->decrement('stock_quantity', $p['qty'] ?? 1);
                        }
                    }
                }
                if (!empty($parsed['others'])) {
                    foreach ($parsed['others'] as $o) {
                        if (empty($o['name'])) continue;
                        DB::table('maintenance_parts')->insert([
                            'maintenance_id' => $maintenance->id,
                            'part_id' => null,
                            'part_name' => $o['name'],
                            'quantity' => 1,
                            'price' => $o['price'] ?? 0,
                            'total' => $o['price'] ?? 0,
                            'created_at' => now(), 'updated_at' => now()
                        ]);
                    }
                }
            }

            return redirect()->route('maintenance.index')->with('success', 'Maintenance record updated successfully');
        });
    }

    public function getParts($id) 
    {
        $parts = DB::table('maintenance_parts')
            ->leftJoin('spare_parts', 'maintenance_parts.part_id', '=', 'spare_parts.id')
            ->where('maintenance_parts.maintenance_id', $id)
            ->select('maintenance_parts.*', 'spare_parts.supplier')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $maintenance = Maintenance::findOrFail($id);
            
            // Return stock back to inventory
            $parts = DB::table('maintenance_parts')->where('maintenance_id', $id)->get();
            foreach ($parts as $p) {
                if ($p->part_id) {
                    DB::table('spare_parts')->where('id', $p->part_id)->increment('stock_quantity', $p->quantity);
                }
            }

            $maintenance->delete();
            return redirect()->route('maintenance.index')->with('success', 'Maintenance record archived and stock returned.');
        });
    }

    public function toggleComplete($id)
    {
        $maint = Maintenance::findOrFail($id);
        
        DB::transaction(function () use ($maint) {
            if ($maint->date_completed) {
                // Uncomplete: Set back to pending and clear date
                $maint->update([
                    'date_completed' => null,
                    'status' => 'pending',
                    'updated_by' => Auth::id()
                ]);
                
                // Set unit status back to maintenance
                DB::table('units')->where('id', $maint->unit_id)->update([
                    'status' => 'maintenance',
                    'updated_at' => now()
                ]);
            } else {
                // Complete: Set status to completed and date to today
                $maint->update([
                    'date_completed' => date('Y-m-d'),
                    'status' => 'completed',
                    'updated_by' => Auth::id()
                ]);
                
                // Set unit status back to active
                DB::table('units')->where('id', $maint->unit_id)->update([
                    'status' => 'active',
                    'updated_at' => now()
                ]);
            }
        });

        $statusMessage = $maint->date_completed ? 'Maintenance marked as completed.' : 'Maintenance marked as incomplete.';
        return back()->with('success', $statusMessage);
    }

    public function toggleInProgress($id)
    {
        $maint = Maintenance::findOrFail($id);

        DB::transaction(function () use ($maint) {
            // Workflow: pending -> in_shop -> testing -> pending
            if ($maint->status === 'pending') {
                $maint->update(['status' => 'in_shop', 'date_completed' => null, 'updated_by' => Auth::id()]);
            } elseif ($maint->status === 'in_shop' || $maint->status === 'in_progress') {
                $maint->update(['status' => 'testing', 'date_completed' => null, 'updated_by' => Auth::id()]);
            } else {
                // If it's testing, revert back to pending to allow restarting the loop if needed
                $maint->update(['status' => 'pending', 'updated_by' => Auth::id()]);
            }
            
            DB::table('units')->where('id', $maint->unit_id)->update(['status' => 'maintenance', 'updated_at' => now()]);
        });

        return back()->with('success', 'Maintenance stage updated.');
    }
}
