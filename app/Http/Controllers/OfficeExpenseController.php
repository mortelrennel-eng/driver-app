<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class OfficeExpenseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $category = $request->input('category', '');
        $date_from = $request->input('date_from', date('Y-m-01'));
        $date_to = $request->input('date_to', date('Y-m-d'));
        $limit = 10;

        $query = DB::table('expenses as e')
            ->whereNull('e.deleted_at')
            ->leftJoin('users as u', 'e.recorded_by', '=', 'u.id')
            ->leftJoin('units as un', 'e.unit_id', '=', 'un.id')
            ->leftJoin('users as creator', 'e.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'e.updated_by', '=', 'editor.id')
            ->select('e.*', 'u.full_name as recorded_by_name', 'un.plate_number', 'creator.full_name as creator_name', 'editor.full_name as editor_name')
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

        // Calculate totals based on the FILTERED query (before pagination)
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

        if ($request->ajax() || $request->wantsJson() || $request->input('format') === 'json') {
            $html = view('office-expenses.partials._expenses_table', compact('expenses'))->render();
            return response()->json([
                'success' => true,
                'html'    => $html,
                'stats'   => $stats,
                'expenses' => $expenses
            ]);
        }

        // Get units for dropdown
        $units = DB::table('units')
            ->where('status', 'active')
            ->select('id', 'plate_number')
            ->orderBy('plate_number')
            ->get();

        $spareParts = \App\Models\SparePart::orderBy('name')->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        $franchises = \App\Models\FranchiseCase::orderBy('case_no')->get();

        return view('office-expenses.index', compact('expenses', 'search', 'category', 'date_from', 'date_to', 'totals', 'categories', 'stats', 'units', 'spareParts', 'suppliers', 'franchises'));
    }

    public function show($id)
    {
        $expense = DB::table('expenses')->where('id', $id)->first();
        return response()->json($expense);
    }

    public function store(Request $request)
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
            'new_part_name' => 'nullable|string|max:30|regex:/^\S.*/',
            'update_master' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1|max:9999',
            'unit_price' => 'nullable|numeric|min:0.01|max:500000',
            'franchise_case_id' => 'nullable|integer',
            'new_expiry_date' => 'nullable|date',
        ]);

        $sparePartId = $request->spare_part_id;
        $finalDescription = $request->description;

        // Franchise Renewal Logic
        if ($request->category === 'Franchise Renewal' && $request->franchise_case_id) {
            $fCase = \App\Models\FranchiseCase::find($request->franchise_case_id);
            if ($fCase) {
                $oldExpiry = $fCase->expiry_date ? $fCase->expiry_date->format('M d, Y') : 'N/A';
                if ($request->new_expiry_date) {
                    $fCase->update(['expiry_date' => $request->new_expiry_date]);
                    $finalDescription = "FRANCHISE RENEWAL: Case #{$fCase->case_no} (Old Expiry: {$oldExpiry} -> New: " . \Carbon\Carbon::parse($request->new_expiry_date)->format('M d, Y') . ")";
                }
            }
        }

        // If it's an existing part but user modified Price or Supplier
        if (is_numeric($sparePartId) && $request->update_master == 1) {
            $existingPart = \App\Models\SparePart::find($sparePartId);
            if ($existingPart) {
                $existingPart->update([
                    'price' => $request->unit_price ?: $existingPart->price,
                    'supplier' => $request->vendor_name ?: $existingPart->supplier
                ]);
            }
        }

        // If it's a new part, register it in inventory first
        if ($sparePartId === 'new' && $request->new_part_name) {
            $newPart = \App\Models\SparePart::create([
                'name' => $request->new_part_name,
                'price' => $request->unit_price ?: 0,
                'stock_quantity' => 0, // Will be incremented below
                'supplier' => $request->vendor_name ?: 'Unspecified Supplier'
            ]);
            $sparePartId = $newPart->id;
            $finalDescription = "REGISTERED & PURCHASED: " . $request->new_part_name;
        }

        $expense = Expense::create([
            'category' => $request->category,
            'description' => $finalDescription,
            'vendor_name' => $request->vendor_name,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'date' => $request->date,
            'reference_number' => $request->reference_number,
            'unit_id' => $request->unit_id ?: null,
            'spare_part_id' => is_numeric($sparePartId) ? $sparePartId : null,
            'franchise_case_id' => $request->franchise_case_id,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'recorded_by' => auth()->id(),
            'created_by' => auth()->id(),
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // If it's a spare parts purchase, increment stock
        if ($request->category === 'Spare Parts Purchase' && $sparePartId && $request->quantity > 0) {
            $part = \App\Models\SparePart::find($sparePartId);
            if ($part) {
                $part->increment('stock_quantity', $request->quantity);
            }
        }

        ActivityLogController::log('Created Office Expense', "Category: {$request->category}\nDescription: {$finalDescription}\nAmount: ₱" . number_format($request->amount, 2));

        return redirect()->route('office-expenses.index')->with('success', 'Expense added successfully');
    }

    public function update(Request $request, $id)
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
        
        // Handle Inventory Reversal if it was a Spare Parts Purchase
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

        // Re-apply Inventory Stock if the updated category is Spare Parts Purchase
        if ($expense->category === 'Spare Parts Purchase' && $expense->spare_part_id && $expense->quantity > 0) {
            $newPart = \App\Models\SparePart::find($expense->spare_part_id);
            if ($newPart) {
                $newPart->increment('stock_quantity', $expense->quantity);
            }
        }

        ActivityLogController::log('Updated Office Expense', "Record #{$id}\nCategory: {$expense->category}\nNew Amount: ₱" . number_format($expense->amount, 2));

        return redirect()->route('office-expenses.index')->with('success', 'Expense updated successfully');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        
        // Reverse inventory stock if applicable
        if ($expense->category === 'Spare Parts Purchase' && $expense->spare_part_id && $expense->quantity > 0) {
            $part = \App\Models\SparePart::find($expense->spare_part_id);
            if ($part) {
                $part->decrement('stock_quantity', $expense->quantity);
            }
        }

        $desc = $expense->description;
        $expense->delete();

        ActivityLogController::log('Archived Office Expense', "Expense: {$desc} moved to archive.");

        return redirect()->route('office-expenses.index')->with('success', 'Expense archived successfully');
    }
}
