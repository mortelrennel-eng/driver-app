<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class SparePartController extends Controller
{
    /**
     * Get all spare parts (API)
     */
    public function index()
    {
        $parts = SparePart::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }

    /**
     * Get purchase history (API)
     */
    public function history()
    {
        $history = DB::table('expenses')
            ->where('category', 'maintenance')
            ->whereNull('deleted_at')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Store or Update a spare part
     * qty_to_add = units being purchased/restocked (always additive)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id'         => 'nullable|integer|exists:spare_parts,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'qty_to_add' => 'nullable|integer|min:0',
            'supplier'   => 'nullable|string|max:255',
        ]);

        $qtyToAdd = (int)($data['qty_to_add'] ?? 0);

        if (isset($data['id'])) {
            // ── UPDATE existing part ──────────────────────────────────────
            $part = SparePart::findOrFail($data['id']);

            // Enforce add-only: never let qty decrease via this form
            if ($qtyToAdd < 0) {
                return response()->json(['success' => false, 'message' => 'Cannot reduce stock from here.'], 422);
            }

            $newStock = (int)($part->stock_quantity ?? 0) + $qtyToAdd;

            $part->update([
                'name'           => $data['name'],
                'price'          => $data['price'],
                'stock_quantity' => $newStock,
                'supplier'       => $data['supplier'] ?? $part->supplier,
            ]);
        } else {
            // ── CREATE new part ───────────────────────────────────────────
            $part = SparePart::create([
                'name'           => $data['name'],
                'price'          => $data['price'],
                'stock_quantity' => $qtyToAdd,
                'supplier'       => $data['supplier'] ?? null,
            ]);
        }

        // ── Auto-record as Office Expense if stock was added ──────────
        $expenseId = null;
        if ($qtyToAdd > 0) {
            $totalCost = $qtyToAdd * (float)$data['price'];
            $userId    = auth()->id() ?? (\App\Models\User::first()->id ?? 18);

            try {
                $expense = \App\Models\Expense::create([
                    'category'         => 'Spare Parts Purchase',
                    'expense_category' => 'Spare Parts Purchase',
                    'spare_part_id'    => $part->id,
                    'quantity'         => $qtyToAdd,
                    'unit_price'       => (float)$data['price'],
                    'description'      => "Inventory STOCK: {$qtyToAdd} pcs of {$part->name}",
                    'vendor_name'      => $part->supplier ?? 'Unspecified Supplier',
                    'amount'           => $totalCost,
                    'date'             => now()->toDateString(),
                    'status'           => 'approved',
                    'recorded_by'      => $userId,
                    'created_by'       => $userId,
                ]);
                $expenseId = $expense->id;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Inventory Expense Record Failed: ' . $e->getMessage());
            }
        }

        $msg = $expenseId
            ? "✅ Stock added! +{$qtyToAdd} pcs of {$part->name} — Purchase recorded in Office Expenses."
            : ($qtyToAdd === 0 ? "Part details updated successfully." : "Stock updated.");

        // Record Activity
        $action = isset($data['id']) ? 'Updated Spare Part' : 'Created Spare Part';
        $logNotes = "Part: {$part->name}\nPrice: ₱" . number_format($part->price, 2);
        if ($qtyToAdd > 0) $logNotes .= "\nStock Added: +{$qtyToAdd} units (New total: {$part->stock_quantity})";
        if ($expenseId) $logNotes .= "\nOffice Expense recorded: #{$expenseId}";
        ActivityLogController::log($action, $logNotes);

        return response()->json([
            'success'          => true,
            'message'          => $msg,
            'expense_recorded' => $expenseId !== null,
            'data'             => $part->fresh(),
        ]);
    }

    /**
     * Get archived spare parts (API)
     */
    public function archived()
    {
        $parts = SparePart::onlyTrashed()->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }

    /**
     * Delete a spare part (Soft Delete)
     */
    public function destroy($id)
    {
        $part = SparePart::findOrFail($id);
        $name = $part->name;
        $part->delete();

        ActivityLogController::log('Archived Spare Part', "Part: {$name} moved to archive.");

        return response()->json([
            'success' => true,
            'message' => 'Part moved to archive successfully'
        ]);
    }

    /**
     * Restore a deleted spare part
     */
    public function restore($id)
    {
        $part = SparePart::withTrashed()->findOrFail($id);
        $part->restore();

        ActivityLogController::log('Restored Spare Part', "Part: {$part->name} restored from archive.");

        return response()->json([
            'success' => true,
            'message' => 'Part restored from archive successfully'
        ]);
    }

    /**
     * Permanently delete a spare part
     */
    public function forceDelete($id)
    {
        $part = SparePart::withTrashed()->findOrFail($id);
        $name = $part->name;
        $part->forceDelete();

        ActivityLogController::log('Permanently Deleted Spare Part', "Part: {$name} was permanently removed from unique system records.");

        return response()->json([
            'success' => true,
            'message' => 'Part permanently removed'
        ]);
    }
}
